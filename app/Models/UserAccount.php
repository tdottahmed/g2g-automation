<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAccount extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_generated_cookies'   => 'boolean',
        'queue_delete_all'       => 'boolean',
        'queue_force_delete_all' => 'boolean',
    ];

    public function offerTemplates()
    {
        return $this->belongsToMany(OfferTemplate::class);
    }

    // Alias kept for legacy callers
    public function offers()
    {
        return $this->belongsToMany(OfferTemplate::class);
    }
}
