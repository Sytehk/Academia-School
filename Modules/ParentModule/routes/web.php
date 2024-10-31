<?php

use Illuminate\Support\Facades\Route;
use Modules\ParentModule\Http\Controllers\WEB\ParentDashboardController;

/*
|--------------------------------------------------------------------------
| Parent Panel (Blade) Routes
|--------------------------------------------------------------------------
|
| Every route is guarded by `auth` (must be logged in) and `parent`
| (user_type must be Parent). Data on each page is scoped to the parent's
| active child inside ParentService, so no per-route child id is trusted
| from the URL.
|
*/

Route::middleware(['auth', 'parent'])
    ->prefix('parent')
    ->name('parent.')
    ->controller(ParentDashboardController::class)
    ->group(function () {
        Route::get('dashboard', 'dashboard')->name('dashboard');

        // Active child switcher.
        Route::post('switch-child', 'switchChild')->name('switch-child');

        // Child-scoped feature pages.
        Route::get('profile', 'profile')->name('profile');
        Route::get('attendance', 'attendance')->name('attendance');
        Route::get('class-routine', 'classRoutine')->name('class-routine');
        Route::get('exam-routine', 'examRoutine')->name('exam-routine');
        Route::get('subjects', 'subjects')->name('subjects');
        Route::get('assignments', 'assignments')->name('assignments');
        Route::get('exam-results', 'examResults')->name('exam-results');

        // Fees.
        Route::get('fees/unpaid', 'feesUnpaid')->name('fees.unpaid');
        Route::get('fees/history', 'paymentHistory')->name('fees.history');
        Route::get('fees/attendance-fine', 'attendanceFine')->name('fees.attendance-fine');

        // Information.
        Route::get('notices', 'notices')->name('notices');
        Route::get('events', 'events')->name('events');
        Route::get('behaviors', 'behaviors')->name('behaviors');
        Route::get('library', 'library')->name('library');
    });
