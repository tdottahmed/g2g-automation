<?php

use App\Http\Controllers\Api\AutomationApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.key')->group(function () {
    Route::get('/automation/heartbeat', [AutomationApiController::class, 'heartbeat']);
    Route::get('/automation/pending', [AutomationApiController::class, 'pending']);
    Route::post('/automation/{template}/success', [AutomationApiController::class, 'success']);
    Route::post('/automation/{template}/failed', [AutomationApiController::class, 'failed']);
});
