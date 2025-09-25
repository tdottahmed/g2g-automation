<?php

use App\Http\Controllers\Admin\ApplicationSetupController;
use App\Http\Controllers\Admin\OfferTemplateController;
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

    Route::get('dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('settings/organization', [ApplicationSetupController::class, 'index'])->name('applicationSetup.index');
    Route::post('settings/organization', [ApplicationSetupController::class, 'update'])->name('applicationSetup.update');

    Route::resource('user-accounts', UserAccountController::class);
    Route::resource('offer-templates', OfferTemplateController::class);
    Route::post('offer-templates/toggle-status/{id}', [OfferTemplateController::class, 'toggleStatus'])->name('offer-templates.toggle-status');
    Route::resource('levels', LevelController::class);
});
