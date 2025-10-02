<?php

namespace App\Jobs;

use App\Models\OfferTemplate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class ProcessOfferTemplate implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 3;
    public $timeout = 600;

    protected $userAccountId;
    protected $templateIds;

    /**
     * Create a new job instance.
     */
    public function __construct($userAccountId, $templateIds)
    {
        $this->userAccountId = $userAccountId;
        $this->templateIds = $templateIds;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $templates = OfferTemplate::whereIn('id', $this->templateIds)->get();

        if ($templates->isEmpty()) {
            Log::error("No templates found for user account: {$this->userAccountId}");
            return;
        }

        $userAccount = $templates->first()->userAccount;
        if (!$userAccount) {
            Log::error("User account not found: {$this->userAccountId}");
            return;
        }

        // Prepare all templates data
        $templatesData = [];

        foreach ($templates as $template) {
            $templateData = $this->prepareTemplateData($template, $userAccount);
            if ($templateData) {
                $templatesData[] = $templateData;
            }
        }

        if (empty($templatesData)) {
            Log::error("No valid template data prepared for user: {$userAccount->email}");
            return;
        }

        // Execute Node.js script with all templates
        $process = new Process([
            'node',
            base_path('scripts/automation/post-offers.js'),
            base64_encode(json_encode($templatesData)),
        ]);

        $process->setWorkingDirectory(base_path('scripts/automation'));
        $process->setTimeout(480);

        $output = '';
        $errorOutput = '';

        $process->run(function ($type, $buffer) use (&$output, &$errorOutput) {
            if ($type === Process::ERR) {
                $errorOutput .= $buffer;
            } else {
                $output .= $buffer;
            }
        });

        if ($process->isSuccessful()) {
            // Update templates on success
            foreach ($templates as $template) {
                $template->update(['last_posted_at' => now()]);

                // Handle offers_to_generate
                if ($template->offers_to_generate && $template->offers_to_generate > 0) {
                    $template->decrement('offers_to_generate');
                    $remainingOffers = $template->fresh()->offers_to_generate;

                    if ($remainingOffers <= 0) {
                        $template->update(['is_active' => false]);
                    }
                }
            }

            Log::info("✅ Successfully processed {$templates->count()} offers for user: {$userAccount->email}");
        } else {
            Log::error("❌ Failed to process offers for user: {$userAccount->email}", [
                'error' => $errorOutput,
                'exit_code' => $process->getExitCode()
            ]);

            throw new \Exception("Node.js process failed: " . $errorOutput);
        }
    }

    private function prepareTemplateData($template, $userAccount)
    {
        try {
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
                                'Link'  => $media['link'] ?? '',
                            ];
                        }
                    }
                }
            }

            $deliveryMethod = is_string($template->delivery_method)
                ? json_decode($template->delivery_method, true)
                : $template->delivery_method;

            $deliveryHour = $deliveryMethod['speed_hour'] ?? "0";
            $deliveryMinute = $deliveryMethod['speed_min'] ?? "30";

            $emailParts = explode('@', $userAccount->email);
            $emailPrefix = $emailParts[0];
            $cookieFile = base_path($emailPrefix . '.json');

            return [
                'template_id' => $template->id,
                'Title' => $template->title ?? 'Untitled Offer',
                'Description' => $template->description ?? '',
                'Town Hall Level' => $template->th_level ?? '',
                'King Level' => $template->king_level ?? '',
                'Queen Level' => $template->queen_level ?? '',
                'Warden Level' => $template->warden_level ?? '',
                'Champion Level' => $template->champion_level ?? '',
                'Default price (unit)' => (string) ($template->price ?? '0'),
                'Minimum purchase quantity' => $template->min_purchase_quantity ?? 1,
                'Instant delivery' => $template->instant_delivery ? 1 : 0,
                'mediaData' => $mediaData,
                'user_email' => $userAccount->email,
                'password' => $userAccount->password,
                'cookies' => $cookieFile,
                'user_id' => $userAccount->id,
                'Delivery hour' => $deliveryHour,
                'Delivery minute' => $deliveryMinute,
            ];
        } catch (\Exception $e) {
            Log::error("Failed to prepare template data: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(\Exception $exception): void
    {
        Log::error("Offer processing failed for user account: {$this->userAccountId}", [
            'error' => $exception->getMessage(),
            'template_ids' => $this->templateIds
        ]);
    }
}
