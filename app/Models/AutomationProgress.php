<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomationProgress extends Model
{
    protected $guarded = [];

    public function template()
    {
        return $this->belongsTo(OfferTemplate::class);
    }
}
