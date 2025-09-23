<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferTemplate extends Model
{
    protected $guarded = [];

    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class);
    }
}
