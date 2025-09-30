<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OfferScheduler extends Model
{
    protected $guarded = [];

    protected $casts = [
        'days' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'posts_today_date' => 'date',
    ];

    /**
     * Relationship to user account (if scheduler is per-account)
     */
    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class);
    }

    /**
     * Relationship to offer template (if scheduler is per-template)
     */
    public function offerTemplate()
    {
        return $this->belongsTo(OfferTemplate::class);
    }

    /**
     * Check if the scheduler is within its active time window
     */
    public function isWithinTimeWindow(): bool
    {
        $now = now($this->timezone);
        $start = $now->copy()->setTimeFromTimeString($this->start_time);
        $end = $now->copy()->setTimeFromTimeString($this->end_time);

        // Handle overnight windows (e.g., 22:00 to 02:00)
        if ($end->lessThanOrEqualTo($start)) {
            $end->addDay();
            if ($now->lessThan($start)) {
                $now = $now->copy()->addDay();
            }
        }

        return $now->betweenIncluded($start, $end);
    }

    /**
     * Check if today is an active day
     */
    public function isActiveToday(): bool
    {
        if (empty($this->days)) {
            return true; // No day restriction
        }

        $todayKey = strtolower(now($this->timezone)->format('D')); // mon, tue, wed, etc.
        $normalizedDays = array_map(fn($d) => strtolower(substr($d, 0, 3)), $this->days);

        return in_array($todayKey, $normalizedDays, true);
    }

    /**
     * Check if daily limit has been reached
     */
    public function hasReachedDailyLimit(): bool
    {
        if (is_null($this->max_posts_per_day)) {
            return false; // No limit
        }

        // Reset counter if it's a new day
        $today = now($this->timezone)->toDateString();
        if ($this->posts_today_date?->toDateString() !== $today) {
            return false;
        }

        return $this->posts_today >= $this->max_posts_per_day;
    }

    /**
     * Check if enough time has passed since last run
     */
    public function canRunNow(): bool
    {
        if (!$this->last_run_at) {
            return true; // Never run before
        }

        $nextRunTime = $this->last_run_at->copy()->addMinutes($this->interval_minutes);
        return now($this->timezone)->greaterThanOrEqualTo($nextRunTime);
    }

    /**
     * Check if the scheduler should run (combines all checks)
     */
    public function shouldRun(): bool
    {
        return $this->is_active
            && $this->isActiveToday()
            && $this->isWithinTimeWindow()
            && !$this->hasReachedDailyLimit()
            && $this->canRunNow();
    }

    /**
     * Increment the post counter
     */
    public function incrementPostCounter(): void
    {
        $today = now($this->timezone)->toDateString();

        if ($this->posts_today_date?->toDateString() !== $today) {
            // New day - reset counter
            $this->posts_today = 1;
            $this->posts_today_date = $today;
        } else {
            // Same day - increment
            $this->posts_today++;
        }

        $this->last_run_at = now($this->timezone);
        $this->save();
    }
}
