<?php

namespace App\Console\Commands;

use App\Jobs\PostOfferTemplate;
use App\Models\OfferTemplate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class RunOfferAutomation extends Command
{
    protected $signature = 'offer:automation';
    protected $description = 'Run offer posting automation';

    public function handle(): void
    {
        $rateLimit = cache()->get('offer_automation_rate_limit', 3);

        // Scheduler gating based on system settings
        $scheduler = getSetting('scheduler')->json ?? null;
        dd($scheduler);
        if (!$this->isNowWithinScheduler($scheduler)) {
            $this->info('Scheduler window is closed. No offers will be dispatched.');
            return;
        }

        $templates = OfferTemplate::with('userAccount')
            ->where('is_active', 1)
            ->orderBy('last_posted_at')
            ->get();

        $dispatched = 0;
        foreach ($templates as $index => $template) {
            if ($dispatched >= $rateLimit) {
                $this->info("Rate limit reached ({$rateLimit}). Stopping.");
                break;
            }
            if (!$template->userAccount) {
                $this->error("No user account found for template ID: {$template->id}");
                continue;
            }
            // need here to run the job
            dispatch(new PostOfferTemplate($template));
            $dispatched++;
            $this->info("Dispatched job for template ID: {$template->title}");
        }
    }

    /**
     * Determine if "now" is within the configured scheduler window.
     * Expected JSON examples:
     *   {"start":"09:00","end":"17:00","timezone":"UTC"}
     *   {"start":"22:00","end":"02:00","timezone":"Europe/Berlin"} // overnight
     * Optionally supports days:
     *   {"start":"09:00","end":"17:00","timezone":"UTC","days":["mon","tue","wed"]}
     */
    private function isNowWithinScheduler($scheduler): bool
    {
        if (empty($scheduler)) {
            // No scheduler configured -> allow all time
            return true;
        }

        // Normalize to array
        if (is_string($scheduler)) {
            $decoded = json_decode($scheduler, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Invalid JSON -> fail open to avoid blocking
                return true;
            }
            $scheduler = $decoded;
        } elseif (is_object($scheduler)) {
            $scheduler = (array) $scheduler;
        }

        $start = $scheduler['start'] ?? null;
        $end = $scheduler['end'] ?? null;
        $tz = $scheduler['timezone'] ?? $scheduler['tz'] ?? config('app.timezone', 'UTC');
        $days = $scheduler['days'] ?? null;

        if (!$start || !$end) {
            // Missing times -> allow all time
            return true;
        }

        // Day filtering (optional)
        if (is_array($days) && count($days) > 0) {
            $todayKey = strtolower(now($tz)->format('D')); // mon,tue,wed,thu,fri,sat,sun
            $normalizedDays = array_map(fn ($d) => strtolower(substr($d, 0, 3)), $days);
            if (!in_array($todayKey, $normalizedDays, true)) {
                return false;
            }
        }

        // Build today's window in the given timezone
        $now = now($tz);
        $startAt = $now->copy()->setTimeFromTimeString($start);
        $endAt = $now->copy()->setTimeFromTimeString($end);

        // Handle overnight window (end earlier than start -> next day)
        if ($endAt->lessThanOrEqualTo($startAt)) {
            $endAt->addDay();
            // If now is before start but after midnight, treat "now" as possibly on the next day window
            if ($now->lessThan($startAt)) {
                $now = $now->copy()->addDay();
            }
        }

        return $now->betweenIncluded($startAt, $endAt);
    }
}
