<?php

namespace App\Console\Commands;

use App\Jobs\PostOfferTemplate;
use App\Models\ApplicationSetup;
use App\Models\OfferAutomationLog;
use App\Models\OfferTemplate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunOfferAutomation extends Command
{
    protected $signature = 'offer:automation';
    protected $description = 'Run offer posting automation using global scheduler settings';

    public function handle(): void
    {
        $this->info('🚀 Starting offer automation...');

        // Get scheduler settings
        $schedulerWindows = json_decode(
            ApplicationSetup::where('type', 'scheduler_windows')->first()->value ?? '[]',
            true
        );
        $scheduleInterval = (int) (
            ApplicationSetup::where('type', 'schedule_interval_minutes')->first()->value ?? 15
        );
        $this->info("⏱️  Interval: {$scheduleInterval} minutes");
        $this->info("🕒 Windows: " . count($schedulerWindows));

       

        $templates = OfferTemplate::where('is_active', 1)->get();
        $this->info("📋 Found {$templates->count()} active template(s)");

        if ($templates->isEmpty()) {
            $this->info("❌ No active templates found.");
            return;
        }

        // Group templates by user account for batch processing
        $templatesByUser = $templates->groupBy('user_account_id');

        $dispatchedJobs = 0;
        $dispatchedTemplates = 0;
        $skippedTemplates = 0;
        $errors = 0;

        foreach ($templatesByUser as $userAccountId => $userTemplates) {
            try {
                $templatesToProcess = [];
                $userEmail = $userTemplates->first()->userAccount->email ?? 'Unknown';

                $this->info("\n👤 Processing user: {$userEmail} (Account ID: {$userAccountId})");
                $this->info("   Found {$userTemplates->count()} templates");

                foreach ($userTemplates as $template) {
                    // Forced post (offers_to_generate > 0)
                    if ($template->offers_to_generate && $template->offers_to_generate > 0) {
                        $templatesToProcess[] = $template;
                        $this->info("   🔥 Adding forced template '{$template->title}' (Offers to generate: {$template->offers_to_generate})");
                        continue;
                    }

                    // Normal scheduled post
                    if ($template->shouldPostNow($scheduleInterval)) {
                        $templatesToProcess[] = $template;
                        $this->info("   ✅ Adding scheduled template '{$template->title}'");
                    } else {
                        $lastPosted = $template->last_posted_at
                            ? $template->last_posted_at->diffForHumans()
                            : 'Never';
                        $this->info("   ⏩ Skipping template '{$template->title}' (Last posted: {$lastPosted})");
                        $skippedTemplates++;
                    }
                }

                if (!empty($templatesToProcess)) {
                    dispatch(new PostOfferTemplate($templatesToProcess));
                    $dispatchedJobs++;
                    $dispatchedTemplates += count($templatesToProcess);
                    $this->info("   📦 Dispatched batch of " . count($templatesToProcess) . " templates for user account {$userAccountId}");
                } else {
                    $this->info("   ℹ️  No templates to process for this user");
                }
            } catch (\Exception $e) {
                $errors++;
                $this->error("❌ Error processing templates for user account {$userAccountId}: {$e->getMessage()}");
                Log::error("Error processing user templates", [
                    'user_account_id' => $userAccountId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        // Summary
        $this->newLine();
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("📊 Automation Summary:");
        $this->info("   • Total templates found: {$templates->count()}");
        $this->info("   • Users processed: " . $templatesByUser->count());
        $this->info("   • Jobs dispatched: {$dispatchedJobs}");
        $this->info("   • Templates dispatched: {$dispatchedTemplates}");
        $this->info("   • Templates skipped: {$skippedTemplates}");
        $this->info("   • Errors: {$errors}");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        if ($dispatchedTemplates > 0) {
            $this->info("✅ Automation completed successfully!");
        } else {
            $this->info("ℹ️  No templates were dispatched for processing.");
        }

        Log::info('Offer automation completed', [
            'dispatched_jobs' => $dispatchedJobs,
            'dispatched_templates' => $dispatchedTemplates,
            'skipped_templates' => $skippedTemplates,
            'errors' => $errors,
            'total_templates' => $templates->count(),
            'total_users' => $templatesByUser->count(),
            'windows' => $schedulerWindows,
            'interval' => $scheduleInterval,
            'current_time' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    private function isWithinSchedulerWindow(array $windows): bool
    {
        if (empty($windows)) {
            $this->info("⚠️  No scheduler windows defined - allowing execution");
            return true;
        }

        $currentTime = now()->format('H:i');
        $this->info("🕐 Current time: {$currentTime}");

        foreach ($windows as $index => $window) {
            if (!isset($window['start']) || !isset($window['end'])) {
                $this->info("⚠️  Invalid window configuration at index {$index}");
                continue;
            }

            $start24 = $this->convertTo24Hour($window['start']);
            $end24 = $this->convertTo24Hour($window['end']);

            $this->info("   Window {$index}: {$window['start']} - {$window['end']} (24h: {$start24} - {$end24})");

            if ($this->isTimeInWindow($currentTime, $start24, $end24)) {
                $this->info("✅ Current time is within window {$index}");
                return true;
            }
        }

        $this->info("❌ Current time is outside all scheduler windows");
        return false;
    }

    private function isTimeInWindow(string $currentTime, string $startTime, string $endTime): bool
    {
        if ($endTime < $startTime) {
            // Window crosses midnight (e.g., 22:00 to 06:00)
            return $currentTime >= $startTime || $currentTime <= $endTime;
        }

        // Normal window within same day
        return $currentTime >= $startTime && $currentTime <= $endTime;
    }

    private function convertTo24Hour(string $time12h): string
    {
        if (empty($time12h)) {
            $this->error("Empty time string provided for conversion");
            return '00:00';
        }

        // If already in 24-hour format, return as is
        if (preg_match('/^\d{1,2}:\d{2}$/', $time12h)) {
            return $time12h;
        }

        try {
            $time = \DateTime::createFromFormat('h:i A', $time12h);
            if ($time) {
                return $time->format('H:i');
            }

            // Try other common formats
            $time = \DateTime::createFromFormat('H:i', $time12h);
            if ($time) {
                return $time->format('H:i');
            }

            $this->error("Failed to parse time: {$time12h}");
            return '00:00';
        } catch (\Exception $e) {
            $this->error("Exception parsing time: {$time12h} - {$e->getMessage()}");
            return '00:00';
        }
    }
}
