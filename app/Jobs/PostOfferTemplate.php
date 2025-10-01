<?php

namespace App\Jobs;

use App\Models\OfferAutomationLog;
use App\Models\OfferTemplate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class PostOfferTemplate implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    protected OfferTemplate $template;

    /**
     * Create a new job instance.
     */
    public function __construct(OfferTemplate $template)
    {
        $this->template = $template;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = microtime(true);
        $executionDetails = [
            'template_id' => $this->template->id,
            'template_title' => $this->template->title,
            'attempt' => $this->attempts(),
            'started_at' => now()->format('Y-m-d H:i:s'),
            'steps' => []
        ];

        try {
            // Step 1: Prepare media data
            $executionDetails['steps']['media_preparation'] = [
                'status' => 'started',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ];

            $mediaData = [];
            if ($this->template->medias) {
                $medias = is_string($this->template->medias)
                    ? json_decode($this->template->medias, true)
                    : $this->template->medias;

                if (json_last_error() === JSON_ERROR_NONE && is_array($medias)) {
                    foreach ($medias as $media) {
                        $mediaData[] = [
                            'title' => $media['title'] ?? 'Media',
                            'Link'  => $media['link'] ?? '',
                        ];
                    }
                    $executionDetails['steps']['media_preparation']['status'] = 'completed';
                    $executionDetails['steps']['media_preparation']['media_count'] = count($mediaData);
                } else {
                    $executionDetails['steps']['media_preparation']['status'] = 'failed';
                    $executionDetails['steps']['media_preparation']['error'] = 'Invalid media data format';
                    throw new \Exception('Invalid media data format');
                }
            } else {
                $executionDetails['steps']['media_preparation']['status'] = 'completed';
                $executionDetails['steps']['media_preparation']['media_count'] = 0;
            }

            // Step 2: Prepare configuration
            $executionDetails['steps']['configuration'] = [
                'status' => 'started',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ];

            $emailParts = explode('@', $this->template->userAccount->email);
            $emailPrefix = $emailParts[0];
            $cookieFile = base_path($emailPrefix . '.json');

            $deliveryMethod = is_string($this->template->delivery_method)
                ? json_decode($this->template->delivery_method, true)
                : $this->template->delivery_method;

            $password = $this->template->userAccount->password;

            $inputData = [
                'Title'                     => $this->template->title,
                'Description'               => $this->template->description,
                'Town Hall Level'           => $this->template->th_level,
                'King Level'                => $this->template->king_level,
                'Queen Level'               => $this->template->queen_level,
                'Warden Level'              => $this->template->warden_level,
                'Champion Level'            => $this->template->champion_level,
                'Default price (unit)'      => (string) $this->template->price,
                'Minimum purchase quantity' => $this->template->min_purchase_quantity,
                'Instant delivery'          => $this->template->instant_delivery ? 1 : 0,
                'mediaData'                 => $mediaData,
                'user_email'                => $this->template->userAccount->email,
                'password'                  => $password,
                'cookies'                   => $cookieFile,
                'user_id'                   => $this->template->userAccount->id,
                'Delivery hour'             => $deliveryMethod['speed_hour'] ?? "0",
                'Delivery minute'           => $deliveryMethod['speed_min'] ?? "30",
            ];

            $executionDetails['steps']['configuration']['status'] = 'completed';
            $executionDetails['steps']['configuration']['user_email'] = $this->template->userAccount->email;

            // Step 3: Execute Node.js script
            $executionDetails['steps']['node_execution'] = [
                'status' => 'started',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ];

            $process = new Process([
                'node',
                base_path('scripts/automation/post-offers.js'),
                base64_encode(json_encode($inputData)),
            ]);

            $process->setWorkingDirectory(base_path('scripts/automation'));
            $process->setTimeout(240);

            $output = '';
            $errorOutput = '';

            $process->run(function ($type, $buffer) use (&$output, &$errorOutput) {
                if ($type === Process::ERR) {
                    Log::error('NODE_ERR: ' . $buffer);
                    $errorOutput .= $buffer;
                } else {
                    Log::info('NODE_OUT: ' . $buffer);
                    $output .= $buffer;
                }
            });

            $executionTime = round(microtime(true) - $startTime, 2);
            $executionDetails['execution_time_seconds'] = $executionTime;

            if ($process->isSuccessful()) {
                $executionDetails['steps']['node_execution']['status'] = 'completed';
                $executionDetails['steps']['node_execution']['exit_code'] = $process->getExitCode();
                $executionDetails['node_output'] = $output;

                // Update template
                $this->template->update(['last_posted_at' => now()]);

                // Handle offers_to_generate
                $remainingOffers = null;
                if ($this->template->offers_to_generate && $this->template->offers_to_generate > 0) {
                    $this->template->decrement('offers_to_generate');
                    $remainingOffers = $this->template->fresh()->offers_to_generate;

                    if ($remainingOffers <= 0) {
                        $this->template->update(['is_active' => false]);
                        $executionDetails['template_deactivated'] = true;
                    }
                }

                $executionDetails['remaining_offers'] = $remainingOffers ?? 'unlimited';
                $executionDetails['completed_at'] = now()->format('Y-m-d H:i:s');

                // Single success log
                OfferAutomationLog::logSuccess(
                    $this->template,
                    "Successfully posted offer for '{$this->template->title}'",
                    $executionDetails
                );

                Log::info('✅ Offer posted successfully', [
                    'template_id' => $this->template->id,
                    'execution_time' => $executionTime,
                ]);
            } else {
                $executionDetails['steps']['node_execution']['status'] = 'failed';
                $executionDetails['steps']['node_execution']['exit_code'] = $process->getExitCode();
                $executionDetails['node_output'] = $output;
                $executionDetails['node_error'] = $errorOutput;
                $executionDetails['failed_at'] = now()->format('Y-m-d H:i:s');

                // Single failure log
                OfferAutomationLog::logFailed(
                    $this->template,
                    "Failed to post offer for '{$this->template->title}'",
                    $executionDetails
                );

                throw new \Exception("Node.js process failed with exit code: " . $process->getExitCode());
            }
        } catch (\Exception $e) {
            $executionTime = round(microtime(true) - $startTime, 2);
            $executionDetails['execution_time_seconds'] = $executionTime;
            $executionDetails['exception'] = $e->getMessage();
            $executionDetails['trace'] = $e->getTraceAsString();
            $executionDetails['failed_at'] = now()->format('Y-m-d H:i:s');

            // Single failure log
            OfferAutomationLog::logFailed(
                $this->template,
                "Exception while posting offer for '{$this->template->title}': {$e->getMessage()}",
                $executionDetails
            );

            Log::error("Offer posting failed", [
                'template_id' => $this->template->id,
                'exception' => $e->getMessage(),
            ]);

            throw $e; // Re-throw for queue retries
        }
    }

    /**
     * Handle permanent job failure.
     */
    public function failed(\Exception $exception): void
    {
        Log::error("Offer posting job failed permanently after {$this->attempts()} attempts", [
            'template_id' => $this->template->id,
            'template_title' => $this->template->title,
            'exception' => $exception->getMessage(),
        ]);
    }
}
