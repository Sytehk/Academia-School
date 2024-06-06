<?php

use App\Http\Controllers\WEB\InstallController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebsiteController::class, 'index'])->name('index')->middleware('checkInstallation');

// ---------------- Installation Process ----------------- //
Route::prefix('install')->middleware('checkInstallationStatus')->group(function () {
    // 1️⃣ Start Installation
    Route::get('/', [InstallController::class, 'index'])->name('install.start');
    Route::get('start', [InstallController::class, 'index']);
    // 2️⃣ Requirements Check
    Route::get('requirements', [InstallController::class, 'requirements'])->name('install.requirements');
    // 3️⃣ Permissions Check
    Route::get('permissions', [InstallController::class, 'permissions'])->name('install.permissions');
    // 4️⃣ Database Setup
    Route::get('database', [InstallController::class, 'showDatabaseForm'])->name('install.database');
    Route::post('check-database', [InstallController::class, 'checkDatabase'])->name('install.check_database');
    Route::post('database/save', [InstallController::class, 'saveDatabase'])->name('install.database.save');
    // 5️⃣ Installation / Migration & Seed
    Route::any('installation', [InstallController::class, 'installation'])->name('install.installation');
});

// ---------------- End Installation ----------------- //
// 6️⃣ Complete / health-check page — only reachable once installed (controller
// also self-checks; the middleware here is defense in depth).
Route::get('install/complete', [InstallController::class, 'complete'])
    ->middleware('checkInstallation')
    ->name('install.complete');

// Upgrade Module — legacy route redirects to new System Update manager
Route::middleware(['auth'])->group(function () {
    Route::get('/upgrade', fn () => redirect()->route('system.update.index'))->name('upgrade.index');
    Route::post('/upgrade', fn () => redirect()->route('system.update.index'));
});

//
Route::get('/page/{slug}', [WebsiteController::class, 'show'])->name('website.page');
Route::get('/student-vital/{encodedData}', [WebsiteController::class, 'vital'])->name('student-vital');
Route::get('institute-cache-clear', [WebsiteController::class, 'instituteCacheClear'])->name('institute.cache.clear');
