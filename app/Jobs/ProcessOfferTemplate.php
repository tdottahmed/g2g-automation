<?php

namespace App\Jobs;

use App\Models\OfferTemplate;
use App\Models\AutomationSession;
use App\Models\AutomationProgress;
use App\Models\OfferAutomationLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class ProcessOfferTemplate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $timeout = 1800;

    protected $templateId;
    protected $sessionId;

    public function __construct($templateId, $sessionId)
    {
        $this->templateId = $templateId;
        $this->sessionId = $sessionId;
    }

    public function handle()
    {
        $template = OfferTemplate::with('userAccount')->find($this->templateId);
        $session = AutomationSession::find($this->sessionId);

        if (!$template || !$session) {
            return;
        }

        $progress = AutomationProgress::where('automation_session_id', $this->sessionId)
            ->where('offer_template_id', $this->templateId)
            ->first();

        if (!$progress) {
            return;
        }

        try {
            // Update progress to processing
            $progress->update([
                'status' => 'processing',
                'started_at' => now(),
                'current_step' => 1,
                'total_steps' => 5,
                'current_action' => 'Preparing template data...',
            ]);

            // Prepare data for Node.js script
            $templateData = $this->prepareTemplateData($template);
            $progress->update(['current_step' => 2, 'current_action' => 'Starting browser session...']);

            // Execute Node.js script
            $process = new Process([
                'node',
                base_path('scripts/automation/post-offers.js'),
                base64_encode(json_encode([$templateData])),
            ]);

            $process->setWorkingDirectory(base_path('scripts/automation'));
            $process->setTimeout(1800);

            $output = '';
            $errorOutput = '';

            $progress->update(['current_step' => 3, 'current_action' => 'Posting offer...']);

            $process->run(function ($type, $buffer) use (&$output, &$errorOutput, $progress) {
                if ($type === Process::ERR) {
                    Log::error('NODE_ERR: ' . $buffer);
                    $errorOutput .= $buffer;
                } else {
                    Log::info('NODE_OUT: ' . $buffer);
                    $output .= $buffer;

                    // Update progress based on Node.js output
                    if (strpos($buffer, 'Processing template') !== false) {
                        $progress->update(['current_action' => 'Filling offer form...']);
                    } elseif (strpos($buffer, 'Form filled successfully') !== false) {
                        $progress->update(['current_step' => 4, 'current_action' => 'Submitting offer...']);
                    } elseif (strpos($buffer, 'Submitting form') !== false) {
                        $progress->update(['current_action' => 'Finalizing submission...']);
                    }
                }
            });

            $progress->update(['current_step' => 5, 'current_action' => 'Finalizing...']);

            if ($process->isSuccessful()) {
                // Success - update template last_posted_at
                $template->update([
                    'last_posted_at' => now(),
                ]);

                $progress->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                // Update session
                $session->increment('processed_templates');
                $session->increment('successful_posts');

                // Handle offers_to_generate if exists
                if (property_exists($template, 'offers_to_generate') && $template->offers_to_generate && $template->offers_to_generate > 0) {
                    $template->decrement('offers_to_generate');
                    if ($template->fresh()->offers_to_generate <= 0) {
                        $template->update(['is_active' => false]);
                    }
                }

                // Log success using your existing logging system
                OfferAutomationLog::create([
                    'offer_template_id' => $template->id,
                    'status' => 'success',
                    'message' => "Successfully posted offer: {$template->title}",
                    'automation_session_id' => $this->sessionId,
                    'details' => json_encode(['node_output' => $output]),
                ]);
            } else {
                throw new \Exception("Node.js process failed: " . $errorOutput);
            }
        } catch (\Exception $e) {
            $this->handleFailure($template, $progress, $session, $e->getMessage());
            throw $e;
        }
    }

    private function prepareTemplateData($template)
    {
        $userAccount = $template->userAccount;
        $emailParts = explode('@', $userAccount->email);
        $emailPrefix = $emailParts[0];
        $cookieFile = base_path("storage/app/cookies/{$emailPrefix}.json");

        // Ensure cookies directory exists
        if (!is_dir(dirname($cookieFile))) {
            mkdir(dirname($cookieFile), 0755, true);
        }

        // Prepare media data
        $mediaData = [];
        if ($template->medias) {
            $medias = is_string($template->medias)
                ? json_decode($template->medias, true)
                : $template->medias;

            if (is_array($medias)) {
                foreach ($medias as $media) {
                    if (!empty($media['link'])) {
                        $mediaData[] = [
                            'title' => $media['title'] ?? 'Media',
                            'Link' => $media['link']
                        ];
                    }
                }
            }
        }

        $deliveryMethod = is_string($template->delivery_method)
            ? json_decode($template->delivery_method, true)
            : $template->delivery_method;

        return [
            'template_id' => $template->id,
            'Title' => $template->title,
            'Description' => $template->description,
            'Town Hall Level' => $template->th_level,
            'King Level' => $template->king_level,
            'Queen Level' => $template->queen_level,
            'Warden Level' => $template->warden_level,
            'Champion Level' => $template->champion_level,
            'Default price (unit)' => (string) $template->price,
            'Minimum purchase quantity' => $template->min_purchase_quantity,
            'Instant delivery' => $template->instant_delivery ? 1 : 0,
            'mediaData' => $mediaData,
            'user_email' => $userAccount->email,
            'password' => $userAccount->password,
            'cookies' => $cookieFile,
            'user_id' => $userAccount->id,
            'Delivery hour' => $deliveryMethod['speed_hour'] ?? "0",
            'Delivery minute' => $deliveryMethod['speed_min'] ?? "30",
            'session_id' => $this->sessionId,
        ];
    }

    private function handleFailure($template, $progress, $session, $error)
    {
        $progress->update([
            'status' => 'failed',
            'error_message' => $error,
            'completed_at' => now(),
        ]);

        $session->increment('processed_templates');
        $session->increment('failed_posts');

        // Log failure using your existing logging system
        OfferAutomationLog::create([
            'offer_template_id' => $template->id,
            'status' => 'failed',
            'message' => "Failed to post offer: {$error}",
            'automation_session_id' => $this->sessionId,
            'details' => json_encode(['error' => $error]),
        ]);
    }

    public function failed(\Exception $exception)
    {
        $template = OfferTemplate::find($this->templateId);
        $progress = AutomationProgress::where('automation_session_id', $this->sessionId)
            ->where('offer_template_id', $this->templateId)
            ->first();
        $session = AutomationSession::find($this->sessionId);

        if ($template && $progress && $session) {
            $this->handleFailure($template, $progress, $session, $exception->getMessage());
        }

        Log::error("ProcessOfferTemplate job failed permanently", [
            'template_id' => $this->templateId,
            'session_id' => $this->sessionId,
            'exception' => $exception->getMessage(),
        ]);
    }
}
