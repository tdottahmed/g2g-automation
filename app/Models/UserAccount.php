<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAccount extends Model
{
    protected $guarded = [];

    public function offers()
    {
        return $this->hasMany(OfferTemplate::class);
    }
}
