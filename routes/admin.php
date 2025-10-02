<?php

use App\Http\Controllers\Admin\ApplicationSetupController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OfferAutomationLogController;
use App\Http\Controllers\Admin\OfferTemplateController;
use App\Http\Controllers\Admin\OfferSchedulerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\UserAccountController;
use App\Http\Controllers\AutomationController;
use App\Http\Controllers\LevelController;

Route::group(['middleware' => ['role:super-admin|admin|staff|user']], function () {
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class)->except('show');
    Route::resource('users', UserController::class);

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('settings/organization', [ApplicationSetupController::class, 'index'])->name('applicationSetup.index');
    Route::post('settings/organization', [ApplicationSetupController::class, 'update'])->name('applicationSetup.update');

    Route::resource('user-accounts', UserAccountController::class);
    Route::resource('offer-templates', OfferTemplateController::class);
    Route::post('offer-templates/toggle-status/{id}', [OfferTemplateController::class, 'toggleStatus'])->name('offer-templates.toggle-status');

    Route::resource('levels', LevelController::class);

    Route::get('offer-logs', [OfferAutomationLogController::class, 'index'])->name('offer-logs.index');
    Route::get('offer-logs/{offerLog}', [OfferAutomationLogController::class, 'show'])->name('offer-logs.show');
    Route::post('offer-logs/clear', [OfferAutomationLogController::class, 'clear'])->name('offer-logs.clear');

    // Automation Routes
    Route::prefix('automation')->group(function () {
        Route::get('/dashboard', [AutomationController::class, 'dashboard'])->name('automation.dashboard');
        Route::get('/user-accounts', [AutomationController::class, 'userAccounts'])->name('automation.user-accounts');
        Route::post('/start', [AutomationController::class, 'start'])->name('automation.start');
        Route::post('/stop', [AutomationController::class, 'stop'])->name('automation.stop');
        Route::get('/status/{userAccountId}', [AutomationController::class, 'status'])->name('automation.status');
        Route::get('/progress/{sessionId}', [AutomationController::class, 'progress'])->name('automation.progress');
        Route::get('/logs', [AutomationController::class, 'logs'])->name('automation.logs');
        Route::get('/session/{sessionId}', [AutomationController::class, 'sessionDetails'])->name('automation.session-details');
    });
});
