<?php

use Illuminate\Support\Facades\Route;
use Modules\Authentication\Http\Controllers\Auth\AuthenticatedSessionController;
use Modules\Authentication\Http\Controllers\Auth\ConfirmablePasswordController;
use Modules\Authentication\Http\Controllers\Auth\EmailVerificationNotificationController;
use Modules\Authentication\Http\Controllers\Auth\EmailVerificationPromptController;
use Modules\Authentication\Http\Controllers\Auth\NewPasswordController;
use Modules\Authentication\Http\Controllers\Auth\PasswordController;
use Modules\Authentication\Http\Controllers\Auth\PasswordResetLinkController;
use Modules\Authentication\Http\Controllers\Auth\RegisteredUserController;
use Modules\Authentication\Http\Controllers\Auth\VerifyEmailController;
use Modules\Authentication\Http\Controllers\ProfileController;
use Modules\Authentication\Http\Controllers\WEB\BranchController;
use Modules\Authentication\Http\Controllers\WEB\DashboardController;
use Modules\Authentication\Http\Controllers\WEB\DomainRequestController;
use Modules\Authentication\Http\Controllers\WEB\ImpersonationController;
use Modules\Authentication\Http\Controllers\WEB\InstituteController;
use Modules\Authentication\Http\Controllers\WEB\LanguageController;
use Modules\Authentication\Http\Controllers\WEB\PlanController;
use Modules\Authentication\Http\Controllers\WEB\RoleController;
use Modules\Authentication\Http\Controllers\WEB\UserController;

Route::get('/language/{lang}', [LanguageController::class, 'index'])->name('language.switch');

Route::get('/impersonate/{token}', [ImpersonationController::class, 'consume'])->name('impersonate.consume');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    Route::resource('plans', PlanController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::post('institutes/{institute}/subscription/update', [InstituteController::class, 'updateSubscription'])->name('institutes.subscription.update');
    Route::post('institutes/{institute}/reset-settings', [InstituteController::class, 'resetSettings'])->name('institutes.reset-settings');
    Route::post('institutes/{institute}/factory-reset', [InstituteController::class, 'factoryReset'])->name('institutes.factory-reset');
    Route::resource('institutes', InstituteController::class);
    Route::resource('branches', BranchController::class)->middleware('plan.limit:branch');

    Route::get('custom-domain', [DomainRequestController::class, 'index'])->name('custom-domain.index');
    Route::post('custom-domain', [DomainRequestController::class, 'store'])->name('custom-domain.store');

    Route::post('stop-impersonating', [ImpersonationController::class, 'stop'])->name('impersonate.stop');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
