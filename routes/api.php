<?php

use App\Http\Controllers\Api\AutomationApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.key')->group(function () {
    Route::get('/automation/heartbeat', [AutomationApiController::class, 'heartbeat']);
    Route::get('/automation/user-accounts', [AutomationApiController::class, 'userAccounts']);
    Route::get('/automation/pending', [AutomationApiController::class, 'pending']);
    Route::post('/automation/{template}/success', [AutomationApiController::class, 'success']);
    Route::post('/automation/{template}/failed', [AutomationApiController::class, 'failed']);

    Route::get('/automation/pending-delete-all', [AutomationApiController::class, 'pendingDeleteAll']);
    Route::post('/automation/user-accounts/{userAccount}/delete-all-complete', [AutomationApiController::class, 'deleteAllComplete']);
    Route::post('/automation/user-accounts/{userAccount}/delete-all-failed', [AutomationApiController::class, 'deleteAllFailed']);
});
