<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAccount extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_generated_cookies' => 'boolean',
    ];

    public function offers()
    {
        return $this->hasMany(OfferTemplate::class);
    }
}
