<?php

use App\Http\Controllers\WEB\SystemInformationController;
use App\Http\Controllers\WEB\SystemModuleController;
use App\Http\Controllers\WEB\SystemSettingsController;
use App\Http\Controllers\WEB\SystemUpdateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('system')->name('system.')->group(function () {
    Route::get('/information', [SystemInformationController::class, 'index'])->name('information');

    Route::get('/update', [SystemUpdateController::class, 'index'])->name('update.index');
    Route::post('/update/check', [SystemUpdateController::class, 'checkUpdates'])->name('update.check');
    Route::post('/update/validate', [SystemUpdateController::class, 'validatePackage'])->name('update.validate');
    Route::post('/update/install', [SystemUpdateController::class, 'install'])->name('update.install');
    Route::get('/update/history', [SystemUpdateController::class, 'history'])->name('update.history');
    Route::get('/update/history/{systemUpdate}', [SystemUpdateController::class, 'show'])->name('update.show');
    Route::post('/update/history/{systemUpdate}/rollback', [SystemUpdateController::class, 'rollback'])->name('update.rollback');

    Route::get('/modules', [SystemModuleController::class, 'index'])->name('modules.index');
    Route::post('/modules/{slug}/enable', [SystemModuleController::class, 'enable'])->name('modules.enable');
    Route::post('/modules/{slug}/disable', [SystemModuleController::class, 'disable'])->name('modules.disable');

    Route::get('/settings', [SystemSettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SystemSettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/migrate', [SystemSettingsController::class, 'migrate'])->name('settings.migrate');
});
