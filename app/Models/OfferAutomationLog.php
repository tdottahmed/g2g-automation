<?php
// app/Models/OfferAutomationLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferAutomationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_template_id',
        'status',
        'message',
        'details',
        'executed_at',
    ];

    protected $casts = [
        'details' => 'array',
        'executed_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(OfferTemplate::class, 'offer_template_id');
    }

    // Simple helper methods
    public static function logSuccess(OfferTemplate $template, string $message, array $details = []): self
    {
        return self::create([
            'offer_template_id' => $template->id,
            'status' => 'success',
            'message' => $message,
            'details' => $details,
            'executed_at' => now(),
        ]);
    }

    public static function logFailed(OfferTemplate $template, string $message, array $details = []): self
    {
        return self::create([
            'offer_template_id' => $template->id,
            'status' => 'failed',
            'message' => $message,
            'details' => $details,
            'executed_at' => now(),
        ]);
    }

    public function userAccount(): BelongsTo
    {
        return $this->belongsTo(UserAccount::class, 'user_account_id');
    }
}
