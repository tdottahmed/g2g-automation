<?php

namespace App\Models;

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

    public function logs()
    {
        return $this->hasMany(OfferAutomationLog::class);
    }
}
