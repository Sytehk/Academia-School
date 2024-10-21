<?php

use Illuminate\Support\Facades\Route;
use Modules\SystemConfiguration\Http\Controllers\WEB\PaymentGatewayController;
use Modules\SystemConfiguration\Http\Controllers\WEB\SAASPaymentGatewayController;
use Modules\SystemConfiguration\Http\Controllers\WEB\UserLogsController;
use Modules\SystemConfiguration\Http\Controllers\WEB\UtilityController;

Route::group(['middleware' => ['auth']], function () {
    // Utility Controller
    Route::match(['get', 'post'], 'administration/general_settings/{store?}', [UtilityController::class, 'settings'])->name('general_settings.update');
    Route::get('administration-change-session/{session_id}', [UtilityController::class, 'change_session'])->name('general_settings.change-session');
    Route::get('administration-change-branch/{branch_id}', [UtilityController::class, 'change_branch']);
    Route::get('administration-backup-database', [UtilityController::class, 'backupDatabase'])->name('utility.administration-backup-database');
    Route::resource('user-logs', UserLogsController::class);
    Route::resource('payment-gateways', PaymentGatewayController::class);
    Route::resource('saas-payment-gateways', SAASPaymentGatewayController::class);
});
