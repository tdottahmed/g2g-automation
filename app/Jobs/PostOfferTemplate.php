<?php

namespace App\Jobs;

use App\Models\OfferAutomationLog;
use App\Models\OfferTemplate;
use App\Models\UserAccount;
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

    public $tries = 1; // Only try once for large batches
    public $timeout = 3600; // 60 minutes for large batches

    protected $templateIds;
    protected $userAccountId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $templateIds, $userAccountId = null)
    {
        $this->templateIds = $templateIds;
        $this->userAccountId = $userAccountId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Increase memory and time limits for large batches
        ini_set('memory_limit', '1024M');
        set_time_limit(3600); // 60 minutes

        $startTime = microtime(true);

        try {
            Log::info('🎯 PostOfferTemplate job started for large batch', [
                'template_ids_count' => count($this->templateIds),
                'user_account_id' => $this->userAccountId,
                'memory_limit' => ini_get('memory_limit'),
                'time_limit' => ini_get('max_execution_time'),
                'job_id' => $this->job->getJobId()
            ]);

            $totalProcessed = 0;
            $totalErrors = 0;

            // Load all templates at once
            $templates = OfferTemplate::with('userAccount')
                ->whereIn('id', $this->templateIds)
                ->get();

            if ($templates->isEmpty()) {
                Log::warning('No templates found for the provided IDs', ['template_ids' => $this->templateIds]);
                return;
            }

            // Group by user account (in case multiple users in one job)
            $templatesByUser = $templates->groupBy('user_account_id');

            foreach ($templatesByUser as $userAccountId => $userTemplates) {
                try {
                    Log::info('👤 Processing user account templates', [
                        'user_account_id' => $userAccountId,
                        'templates_count' => $userTemplates->count()
                    ]);

                    $processed = $this->processUserTemplates($userTemplates, $userAccountId, $startTime);
                    $totalProcessed += $processed;

                    // Add a small delay between user accounts to prevent rate limiting
                    if (next($templatesByUser)) {
                        sleep(3);
                    }
                } catch (\Exception $e) {
                    $totalErrors += $userTemplates->count();
                    Log::error("❌ Failed to process templates for user account {$userAccountId}", [
                        'error' => $e->getMessage(),
                        'templates_count' => $userTemplates->count(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    // Continue with next user account even if one fails
                    continue;
                }
            }

            $executionTime = round(microtime(true) - $startTime, 2);
            $successRate = count($this->templateIds) > 0 ? round(($totalProcessed / count($this->templateIds)) * 100, 2) : 0;

            Log::info('🏁 Large batch offer processing completed', [
                'total_processed' => $totalProcessed,
                'total_errors' => $totalErrors,
                'total_templates' => count($this->templateIds),
                'execution_time_seconds' => $executionTime,
                'success_rate' => $successRate . '%',
                'job_id' => $this->job->getJobId()
            ]);

            // Mark job as successfully completed
            $this->markJobAsCompleted();
        } catch (\Exception $e) {
            $executionTime = round(microtime(true) - $startTime, 2);
            Log::error('💥 PostOfferTemplate job failed for large batch', [
                'template_ids' => $this->templateIds,
                'user_account_id' => $this->userAccountId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'execution_time_seconds' => $executionTime,
                'job_id' => $this->job->getJobId()
            ]);

            $this->markJobAsFailed();
            throw $e;
        }
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

        $tempFile = null;

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

            // Step 2: Create temporary file with all templates data
            $tempFile = tempnam(sys_get_temp_dir(), 'large_batch_offer_');
            file_put_contents($tempFile, json_encode($templatesData));

            Log::info('💾 Large batch data written to temporary file', [
                'file' => $tempFile,
                'templates_count' => count($templatesData),
                'file_size_kb' => round(filesize($tempFile) / 1024, 2),
                'user_account_id' => $userAccountId
            ]);

            // Step 3: Execute Node.js script with file path
            $executionDetails['steps']['node_execution'] = [
                'status' => 'started',
                'timestamp' => now()->format('Y-m-d H:i:s')
            ];

            $nodeScriptPath = base_path('scripts/automation/post-offers.js');

            if (!file_exists($nodeScriptPath)) {
                throw new \Exception("Node.js script not found at: {$nodeScriptPath}");
            }

            // Use larger timeout for big batches
            $process = new Process([
                'node',
                $nodeScriptPath,
                $tempFile,
            ]);

            $process->setWorkingDirectory(base_path('scripts/automation'));
            $process->setTimeout(2700); // 45 minutes for large batches

            $output = '';
            $errorOutput = '';

            $process->run(function ($type, $buffer) use (&$output, &$errorOutput) {
                if ($type === Process::ERR) {
                    Log::error('NODE_ERR: ' . trim($buffer));
                    $errorOutput .= $buffer;
                } else {
                    // Only log important Node.js output to avoid too many logs
                    if (
                        strpos($buffer, '✅') !== false ||
                        strpos($buffer, '❌') !== false ||
                        strpos($buffer, '🔄') !== false ||
                        strpos($buffer, '📊') !== false
                    ) {
                        Log::info('NODE_OUT: ' . trim($buffer));
                    }
                    $output .= $buffer;
                }
            });

            $executionTime = round(microtime(true) - $startTime, 2);
            $executionDetails['execution_time_seconds'] = $executionTime;

            Log::info('🔚 Node.js process for large batch completed', [
                'exit_code' => $process->getExitCode(),
                'successful' => $process->isSuccessful(),
                'output_length' => strlen($output),
                'error_output_length' => strlen($errorOutput),
                'execution_time' => $executionTime,
                'user_account_id' => $userAccountId
            ]);

            if ($process->isSuccessful()) {
                $executionDetails['steps']['node_execution']['status'] = 'completed';
                $executionDetails['steps']['node_execution']['exit_code'] = $process->getExitCode();
                $executionDetails['node_output'] = $output;

                // Parse results from Node.js output
                $successCount = $this->parseNodeResults($output, count($templatesData));

                // Update templates and handle offers_to_generate
                foreach ($successfulTemplates as $index => $template) {
                    // Only mark as successful if within the success count
                    if ($index < $successCount) {
                        $template->update(['last_posted_at' => now()]);
                        // Update execution details for this template
                        foreach ($executionDetails['templates'] as &$templateDetail) {
                            if ($templateDetail['id'] === $template->id) {
                                $templateDetail['status'] = 'completed';
                                $templateDetail['remaining_offers'] = $remainingOffers ?? 'unlimited';
                                $templateDetail['completed_at'] = now()->format('Y-m-d H:i:s');
                            }
                        }
                    } else {
                        // Mark as failed if beyond success count
                        foreach ($executionDetails['templates'] as &$templateDetail) {
                            if ($templateDetail['id'] === $template->id) {
                                $templateDetail['status'] = 'failed';
                                $templateDetail['error'] = 'Node.js process reported failure';
                            }
                        }
                    }
                }

                $executionDetails['completed_at'] = now()->format('Y-m-d H:i:s');

                // Log success for the batch
                OfferAutomationLog::logSuccess(
                    $firstTemplate,
                    "Successfully posted {$successCount} offers for user {$userAccount->email} in large batch",
                    $executionDetails
                );

                Log::info('✅ Large batch offers posted successfully', [
                    'user_account_id' => $userAccountId,
                    'templates_count' => $successCount,
                    'execution_time' => $executionTime,
                    'user_email' => $userAccount->email
                ]);

                return $successCount;
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

                $errorMessage = "Node.js process failed with exit code: " . $process->getExitCode();
                if (!empty($errorOutput)) {
                    $errorMessage .= ". Error: " . $errorOutput;
                } elseif (!empty($output)) {
                    $errorMessage .= ". Output: " . $output;
                } else {
                    $errorMessage .= ". No output captured.";
                }

                OfferAutomationLog::logFailed(
                    $firstTemplate,
                    "Failed to post offers for user {$userAccount->email} in large batch. " . $errorMessage,
                    $executionDetails
                );

                throw new \Exception($errorMessage);
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
                "Exception while posting offers for user account {$userAccountId} in large batch: {$e->getMessage()}",
                $executionDetails
            );

            Log::error("💥 Large batch offers posting failed for user account", [
                'user_account_id' => $userAccountId,
                'exception' => $e->getMessage(),
                'templates_count' => $templates->count()
            ]);

            throw $e;
        } finally {
            // Clean up temporary file
            if ($tempFile && file_exists($tempFile)) {
                unlink($tempFile);
                Log::info('🧹 Temporary file cleaned up', ['file' => $tempFile]);
            }
        }
    }

    /**
     * Parse Node.js results from output
     */
    private function parseNodeResults($output, $totalTemplates)
    {
        // Look for RESULTS:successCount:failCount pattern
        if (preg_match('/RESULTS:(\d+):(\d+)/', $output, $matches)) {
            $successCount = (int) $matches[1];
            $failCount = (int) $matches[2];

            Log::info('📊 Parsed Node.js results', [
                'success_count' => $successCount,
                'fail_count' => $failCount,
                'total_templates' => $totalTemplates
            ]);

            return $successCount;
        }

        // If no results pattern found, assume all succeeded for backward compatibility
        Log::warning('No RESULTS pattern found in Node.js output, assuming all templates succeeded');
        return $totalTemplates;
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
                'Delivery hour' => $deliveryHour
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
     * Mark job as completed in cache for monitoring
     */
    private function markJobAsCompleted(): void
    {
        $jobSignature = 'job_completed:' . $this->job->getJobId();
        cache([$jobSignature => [
            'completed_at' => now()->toDateTimeString(),
            'templates_count' => count($this->templateIds),
            'user_account_id' => $this->userAccountId,
            'status' => 'completed'
        ]], now()->addHours(2));

        Log::info('🏷️ Job marked as completed in cache', [
            'job_id' => $this->job->getJobId(),
            'completed_at' => now()->toDateTimeString()
        ]);
    }

    /**
     * Mark job as failed in cache for monitoring
     */
    private function markJobAsFailed(): void
    {
        $jobSignature = 'job_completed:' . $this->job->getJobId();
        cache([$jobSignature => [
            'failed_at' => now()->toDateTimeString(),
            'templates_count' => count($this->templateIds),
            'user_account_id' => $this->userAccountId,
            'status' => 'failed'
        ]], now()->addHours(2));

        Log::info('🏷️ Job marked as failed in cache', [
            'job_id' => $this->job->getJobId(),
            'failed_at' => now()->toDateTimeString()
        ]);
    }

    /**
     * Get the display name for the job.
     */
    public function displayName(): string
    {
        return 'Post Large Batch Offer Templates (' . count($this->templateIds) . ' templates)';
    }

    /**
     * Get the job's tags.
     */
    public function tags(): array
    {
        return [
            'offer-posting',
            'large-batch',
            'user:' . ($this->userAccountId ?? 'unknown'),
            'templates:' . count($this->templateIds)
        ];
    }

    /**
     * Handle permanent job failure.
     */
    public function failed(\Exception $exception): void
    {
        Log::error("💥 Large batch offers posting job failed permanently", [
            'template_ids' => $this->templateIds,
            'user_account_id' => $this->userAccountId,
            'templates_count' => count($this->templateIds),
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'job_id' => $this->job->getJobId()
        ]);

        // Load templates to log failures
        $templates = OfferTemplate::whereIn('id', $this->templateIds)->get();

        foreach ($templates as $template) {
            OfferAutomationLog::logFailed(
                $template,
                "Large batch job failed permanently: {$exception->getMessage()}",
                [
                    'attempts' => $this->attempts(),
                    'exception' => $exception->getMessage(),
                    'failed_at' => now()->format('Y-m-d H:i:s'),
                    'job_id' => $this->job->getJobId()
                ]
            );
        }

        $this->markJobAsFailed();
    }
}
