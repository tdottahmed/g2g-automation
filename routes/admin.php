<?php

use App\Http\Controllers\Admin\ApplicationSetupController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OfferAutomationController;
use App\Http\Controllers\Admin\OfferAutomationLogController;
use App\Http\Controllers\Admin\OfferTemplateController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\UserAccountController;
Route::group(['middleware' => ['role:super-admin|admin|staff|user']], function () {
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class)->except('show');
    Route::resource('users', UserController::class);

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('settings/organization', [ApplicationSetupController::class, 'index'])->name('applicationSetup.index');
    Route::post('settings/organization', [ApplicationSetupController::class, 'update'])->name('applicationSetup.update');

    Route::resource('user-accounts', UserAccountController::class);
    Route::post('user-accounts/{userAccount}/queue-delete-all', [UserAccountController::class, 'queueDeleteAll'])->name('user-accounts.queue-delete-all');
    Route::post('user-accounts/{userAccount}/queue-delete-non-permanent', [UserAccountController::class, 'queueDeleteNonPermanent'])->name('user-accounts.queue-delete-non-permanent');
    Route::post('user-accounts/{userAccount}/queue-force-delete-all', [UserAccountController::class, 'queueForceDeleteAll'])->name('user-accounts.queue-force-delete-all');
    Route::post('offer-templates/bulk-action', [OfferTemplateController::class, 'bulkAction'])->name('offer-templates.bulk-action');
    Route::post('offer-templates/queue-post-by-account', [OfferTemplateController::class, 'queuePostByAccount'])->name('offer-templates.queue-post-by-account');
    Route::post('offer-templates/queue-post-all-accounts', [OfferTemplateController::class, 'queuePostAllAccounts'])->name('offer-templates.queue-post-all-accounts');
    Route::post('offer-templates/{offerTemplate}/toggle-permanent', [OfferTemplateController::class, 'togglePermanent'])->name('offer-templates.toggle-permanent');
    Route::resource('offer-templates', OfferTemplateController::class);
    Route::post('offer-templates/{offerTemplate}/queue-post', [OfferTemplateController::class, 'queuePost'])->name('offer-templates.queue-post');

    Route::get('offer-logs', [OfferAutomationLogController::class, 'index'])->name('offer-logs.index');
    Route::get('offer-logs/{offerLog}', [OfferAutomationLogController::class, 'show'])->name('offer-logs.show');
    Route::post('offer-logs/clear', [OfferAutomationLogController::class, 'clear'])->name('offer-logs.clear');

    Route::prefix('automation')->group(function () {
        Route::get('/dashboard', [OfferAutomationController::class, 'dashboard'])->name('automation.dashboard');
        Route::get('/user/{userAccount}/templates', [OfferAutomationController::class, 'getUserTemplates'])->name('automation.user.templates');
    });
});
