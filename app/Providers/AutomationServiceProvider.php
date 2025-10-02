<?php

namespace App\Providers;

use App\Services\AutomationService;
use Illuminate\Support\ServiceProvider;

class AutomationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AutomationService::class, function ($app) {
            return new AutomationService();
        });
    }

    public function boot()
    {
        // Optional: Register console commands if needed
    }
}
