<?php

namespace App\Jobs;

use App\Models\OfferAutomationLog;
use App\Models\OfferTemplate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class PostOfferTemplate implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 3;
    public $timeout = 600; // Increased timeout for multiple offers

    protected $templates;

    /**
     * Create a new job instance.
     */
    public function __construct($templates)
    {
        $this->templates = collect($templates);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = microtime(true);

        // Group templates by user account to process in batches
        $templatesByUser = $this->templates->groupBy('user_account_id');
        Log::info('Processing templates by user account', [
            'templates_by_user' => $templatesByUser->count()
        ]);


        $totalProcessed = 0;
        $totalErrors = 0;

        foreach ($templatesByUser as $userAccountId => $userTemplates) {
            try {
                $processed = $this->processUserTemplates($userTemplates, $userAccountId, $startTime);
                $totalProcessed += $processed;
            } catch (\Exception $e) {
                $totalErrors++;
                Log::error("Failed to process templates for user account {$userAccountId}", [
                    'error' => $e->getMessage(),
                    'templates_count' => $userTemplates->count()
                ]);
            }
        }

        Log::info('Multiple offers processing completed', [
            'total_processed' => $totalProcessed,
            'total_errors' => $totalErrors,
            'total_user_accounts' => $templatesByUser->count()
        ]);
    }

    private function processUserTemplates($templates, $userAccountId, $startTime)
    {
        $executionDetails = [
            'user_account_id' => $userAccountId,
            'templates_count' => $templates->count(),
            'attempt' => $this->attempts(),
            'started_at' => now()->format('Y-m-d H:i:s'),
            'templates' => []
        ];

        try {
            $firstTemplate = $templates->first();
            $userAccount = $firstTemplate->userAccount;

            if (!$userAccount) {
                throw new \Exception("User account not found for ID: {$userAccountId}");
            }

            // Step 1: Prepare configuration for all templates
            $executionDetails['steps']['configuration'] = [
                'status' => 'started',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ];

            $emailParts = explode('@', $userAccount->email);
            $emailPrefix = $emailParts[0];
            $cookieFile = base_path($emailPrefix . '.json');

            $password = $userAccount->password;

            // Prepare all templates data
            $templatesData = [];
            $successfulTemplates = [];

            foreach ($templates as $template) {
                $templateData = $this->prepareTemplateData($template, $userAccount, $cookieFile);
                if ($templateData) {
                    $templatesData[] = $templateData;
                    $successfulTemplates[] = $template;
                    $executionDetails['templates'][] = [
                        'id' => $template->id,
                        'title' => $template->title,
                        'status' => 'prepared'
                    ];
                } else {
                    $executionDetails['templates'][] = [
                        'id' => $template->id,
                        'title' => $template->title,
                        'status' => 'failed',
                        'error' => 'Failed to prepare template data'
                    ];
                }
            }

            if (empty($templatesData)) {
                throw new \Exception('No valid templates to process after preparation');
            }

            $executionDetails['steps']['configuration']['status'] = 'completed';
            $executionDetails['steps']['configuration']['templates_prepared'] = count($templatesData);

            // Step 2: Execute Node.js script with all templates
            $executionDetails['steps']['node_execution'] = [
                'status' => 'started',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ];

            $process = new Process([
                'node',
                base_path('scripts/automation/post-offers.js'),
                base64_encode(json_encode($templatesData)),
            ]);

            $process->setWorkingDirectory(base_path('scripts/automation'));
            $process->setTimeout(480); // Increased timeout for multiple offers

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

                // Update templates and handle offers_to_generate
                foreach ($successfulTemplates as $template) {
                    $template->update(['last_posted_at' => now()]);

                    // Handle offers_to_generate
                    $remainingOffers = null;
                    if ($template->offers_to_generate && $template->offers_to_generate > 0) {
                        $template->decrement('offers_to_generate');
                        $remainingOffers = $template->fresh()->offers_to_generate;

                        if ($remainingOffers <= 0) {
                            $template->update(['is_active' => false]);
                            $executionDetails['template_deactivated'] = true;
                        }
                    }

                    // Update execution details for this template
                    foreach ($executionDetails['templates'] as &$templateDetail) {
                        if ($templateDetail['id'] === $template->id) {
                            $templateDetail['status'] = 'completed';
                            $templateDetail['remaining_offers'] = $remainingOffers ?? 'unlimited';
                            $templateDetail['completed_at'] = now()->format('Y-m-d H:i:s');
                        }
                    }
                }

                $executionDetails['completed_at'] = now()->format('Y-m-d H:i:s');

                // Log success for the batch
                OfferAutomationLog::logSuccess(
                    $firstTemplate,
                    "Successfully posted " . count($successfulTemplates) . " offers for user {$userAccount->email}",
                    $executionDetails
                );

                Log::info('✅ Multiple offers posted successfully', [
                    'user_account_id' => $userAccountId,
                    'templates_count' => count($successfulTemplates),
                    'execution_time' => $executionTime,
                    'user_email' => $userAccount->email
                ]);

                return count($successfulTemplates);
            } else {
                $executionDetails['steps']['node_execution']['status'] = 'failed';
                $executionDetails['steps']['node_execution']['exit_code'] = $process->getExitCode();
                $executionDetails['node_output'] = $output;
                $executionDetails['node_error'] = $errorOutput;
                $executionDetails['failed_at'] = now()->format('Y-m-d H:i:s');

                foreach ($executionDetails['templates'] as &$templateDetail) {
                    $templateDetail['status'] = 'failed';
                    $templateDetail['error'] = 'Node.js process failed';
                }

                OfferAutomationLog::logFailed(
                    $firstTemplate,
                    "Failed to post offers for user {$userAccount->email}. Exit code: " . $process->getExitCode(),
                    $executionDetails
                );

                throw new \Exception("Node.js process failed with exit code: " . $process->getExitCode() . ". Error: " . $errorOutput);
            }
        } catch (\Exception $e) {
            $executionTime = round(microtime(true) - $startTime, 2);
            $executionDetails['execution_time_seconds'] = $executionTime;
            $executionDetails['exception'] = $e->getMessage();
            $executionDetails['trace'] = $e->getTraceAsString();
            $executionDetails['failed_at'] = now()->format('Y-m-d H:i:s');

            foreach ($executionDetails['templates'] as &$templateDetail) {
                $templateDetail['status'] = 'failed';
                $templateDetail['error'] = $e->getMessage();
            }

            OfferAutomationLog::logFailed(
                $templates->first() ?? new OfferTemplate(),
                "Exception while posting offers for user account {$userAccountId}: {$e->getMessage()}",
                $executionDetails
            );

            Log::error("Multiple offers posting failed for user account", [
                'user_account_id' => $userAccountId,
                'exception' => $e->getMessage(),
                'templates_count' => $templates->count()
            ]);

            throw $e;
        }
    }

    private function prepareTemplateData($template, $userAccount, $cookieFile)
    {
        try {
            // Prepare media data
            $mediaData = [];
            if ($template->medias) {
                $medias = is_string($template->medias)
                    ? json_decode($template->medias, true)
                    : $template->medias;

                if (json_last_error() === JSON_ERROR_NONE && is_array($medias)) {
                    foreach ($medias as $media) {
                        if (!empty($media['link'])) {
                            $mediaData[] = [
                                'title' => $media['title'] ?? 'Media',
                                'Link'  => $media['link'] ?? '',
                            ];
                        }
                    }
                } else {
                    Log::warning('Invalid media data format for template', [
                        'template_id' => $template->id,
                        'medias' => $template->medias
                    ]);
                }
            }

            $deliveryMethod = is_string($template->delivery_method)
                ? json_decode($template->delivery_method, true)
                : $template->delivery_method;

            // Ensure we have valid delivery times
            $deliveryHour = $deliveryMethod['speed_hour'] ?? "0";
            $deliveryMinute = $deliveryMethod['speed_min'] ?? "30";

            // Validate required fields
            $requiredFields = [
                'title' => $template->title,
                'description' => $template->description,
                'th_level' => $template->th_level,
                'price' => $template->price
            ];

            foreach ($requiredFields as $field => $value) {
                if (empty($value)) {
                    Log::warning("Required field '{$field}' is empty for template", [
                        'template_id' => $template->id,
                        'field' => $field
                    ]);
                }
            }

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
            Log::error("Failed to prepare template data", [
                'template_id' => $template->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Get the display name for the job.
     */
    public function displayName(): string
    {
        return 'Post Multiple Offer Templates';
    }

    /**
     * Get the job's tags.
     */
    public function tags(): array
    {
        $templateIds = $this->templates->pluck('id')->toArray();
        return ['offer-posting', 'multiple', 'templates:' . implode(',', $templateIds)];
    }

    /**
     * Handle permanent job failure.
     */
    public function failed(\Exception $exception): void
    {
        $templateIds = $this->templates->pluck('id')->toArray();
        $templateTitles = $this->templates->pluck('title')->toArray();

        Log::error("Multiple offers posting job failed permanently after {$this->attempts()} attempts", [
            'template_ids' => $templateIds,
            'template_titles' => $templateTitles,
            'templates_count' => $this->templates->count(),
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Log failure for each template
        foreach ($this->templates as $template) {
            OfferAutomationLog::logFailed(
                $template,
                "Job failed permanently after {$this->attempts()} attempts: {$exception->getMessage()}",
                [
                    'attempts' => $this->attempts(),
                    'exception' => $exception->getMessage(),
                    'failed_at' => now()->format('Y-m-d H:i:s')
                ]
            );
        }
    }
}
