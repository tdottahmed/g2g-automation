<?php

use App\Http\Controllers\Admin\ApplicationSetupController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OfferAutomationController;
use App\Http\Controllers\Admin\OfferAutomationLogController;
use App\Http\Controllers\Admin\OfferTemplateController;
use App\Http\Controllers\Admin\OfferSchedulerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\UserAccountController;
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

    // routes/web.php
    Route::prefix('automation')->group(function () {
        Route::get('/dashboard', [OfferAutomationController::class, 'dashboard'])->name('automation.dashboard');
        Route::post('/run/user/{userAccount}', [OfferAutomationController::class, 'runForUser'])->name('automation.run.user');
        Route::post('/run/template/{template}', [OfferAutomationController::class, 'runForTemplate'])->name('automation.run.template');
        Route::get('/user/{userAccount}/templates', [OfferAutomationController::class, 'getUserTemplates'])->name('automation.user.templates');
    });
});
