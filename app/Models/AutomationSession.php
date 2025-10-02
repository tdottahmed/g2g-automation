<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomationSession extends Model
{
    protected $guarded = [];

    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class);
    }

    public function progress()
    {
        return $this->hasMany(AutomationProgress::class);
    }
}
