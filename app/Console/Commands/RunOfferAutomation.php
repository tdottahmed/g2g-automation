<?php

namespace App\Console\Commands;

use App\Jobs\PostOfferTemplate;
use App\Models\ApplicationSetup;
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

        // Check if current time is within any active window (no day check)
        if (!$this->isWithinSchedulerWindow($schedulerWindows)) {
            $this->info('⏸️  Current time is outside scheduler windows. No offers will be dispatched.');
            return;
        }

        $templates = OfferTemplate::where('is_active', 1)->get();
        $this->info("📋 Found {$templates->count()} active template(s)");

        $dispatched = 0;
        $skipped = 0;

        foreach ($templates as $template) {
            if ($template->offers_to_generate) {
                dispatch(new PostOfferTemplate($template));
                $dispatched++;

                $template->update([
                    'offers_to_generate' => false,
                    'last_posted_at' => now(),
                ]);

                $this->info("🔥 Forced dispatch for '{$template->title}' (ID: {$template->id})");
                continue;
            }

            // Normal scheduled post - check interval
            if (!$template->shouldPostNow($scheduleInterval)) {
                $this->info("⏩ Skipping template '{$template->title}' (ID: {$template->id}) - interval not reached");
                $skipped++;
                continue;
            }

            dispatch(new PostOfferTemplate($template));
            $dispatched++;

            $template->update(['last_posted_at' => now()]);

            $this->info("✅ Dispatched job for '{$template->title}' (ID: {$template->id})");
        }

        // Summary
        $this->newLine();
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("📊 Summary:");
        $this->info("   • Templates processed: {$templates->count()}");
        $this->info("   • Jobs dispatched: {$dispatched}");
        $this->info("   • Templates skipped: {$skipped}");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        Log::info('Offer automation completed', [
            'dispatched' => $dispatched,
            'skipped' => $skipped,
            'total_templates' => $templates->count(),
            'windows' => $schedulerWindows,
            'interval' => $scheduleInterval,
            'current_time' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    private function isWithinSchedulerWindow(array $windows): bool
    {
        $currentTime = now()->format('H:i'); // Current time in 24h format
        foreach ($windows as $window) {
            $start24 = $this->convertTo24Hour($window['start']);
            $end24 = $this->convertTo24Hour($window['end']);
            if ($this->isTimeInWindow($currentTime, $start24, $end24)) {
                return true;
            }
        }

        return false;
    }

    private function isTimeInWindow(string $currentTime, string $startTime, string $endTime): bool
    {
        if ($endTime < $startTime) {
            return $currentTime >= $startTime || $currentTime <= $endTime;
        }
        return $currentTime >= $startTime && $currentTime <= $endTime;
    }

    private function convertTo24Hour(string $time12h): string
    {
        if (empty($time12h)) {
            return '00:00';
        }

        // If already in 24h format, return as is
        if (preg_match('/^\d{1,2}:\d{2}$/', $time12h)) {
            return $time12h;
        }

        try {
            $time = \DateTime::createFromFormat('h:i A', $time12h);
            return $time ? $time->format('H:i') : '00:00';
        } catch (\Exception $e) {
            $this->error("Failed to parse time: {$time12h}");
            return '00:00';
        }
    }
}
