<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OfferTemplate extends Model
{
    protected $guarded = [];

    protected $casts = [
        'medias' => 'array',
        'delivery_method' => 'array',
        'wholesale_pricing' => 'array',
        'is_active' => 'boolean',
        'enable_low_stock_alert' => 'boolean',
        'instant_delivery' => 'boolean',
        'enable_wholesale_pricing' => 'boolean',
        'last_posted_at' => 'datetime',
    ];

    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class);
    }

    /**
     * Get the scheduler for this template (template-specific)
     */
    public function scheduler()
    {
        return $this->hasOne(OfferScheduler::class);
    }


    public function shouldPostNow(int $intervalMinutes): bool
    {
        // If never posted before, it should post (during scheduler windows)
        if ($this->last_posted_at === null) {
            return true;
        }

        // Check if enough time has passed since last post
        $nextPostTime = $this->last_posted_at->addMinutes($intervalMinutes);

        return now()->gte($nextPostTime);
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

        if (preg_match('/^\d{1,2}:\d{2}$/', $time12h)) {
            return $time12h;
        }

        try {
            $time = \DateTime::createFromFormat('h:i A', $time12h);
            return $time ? $time->format('H:i') : '00:00';
        } catch (\Exception $e) {
            return '00:00';
        }
    }

    public function logs()
    {
        return $this->hasMany(OfferAutomationLog::class);
    }
}
