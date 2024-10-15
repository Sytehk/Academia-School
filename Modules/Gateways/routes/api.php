<?php

use Illuminate\Support\Facades\Route;
use Modules\Gateways\Http\Controllers\API\PaymentController;

Route::middleware('auth:api')->group(function () {
    // Digital Payment API
    Route::get('digital-payment', [PaymentController::class, 'index']);
    Route::put('digital-payment/{settingId}', [PaymentController::class, 'update']);
    Route::post('digital-payment', [PaymentController::class, 'payment']);
    Route::get('digital-payment-status/{settingId}', [PaymentController::class, 'statusUpdate']);

    Route::get('saas-digital-payment', [PaymentController::class, 'saasIndex']);
    Route::put('saas-digital-payment/{settingId}', [PaymentController::class, 'saasUpdate']);
    Route::get('saas-digital-payment-status/{settingId}', [PaymentController::class, 'saasStatusUpdate']);
});
