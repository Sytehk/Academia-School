<?php

use Illuminate\Support\Facades\Route;
use Modules\SMS\Http\Controllers\API\PhoneBookCategoryController;
use Modules\SMS\Http\Controllers\API\PhoneBookController;
use Modules\SMS\Http\Controllers\API\SmsController;
use Modules\SMS\Http\Controllers\API\SmsPurchaseController;
use Modules\SMS\Http\Controllers\API\SmsTemplateController;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('sms-template', SmsTemplateController::class);
    Route::apiResource('phone-book-category', PhoneBookCategoryController::class);
    Route::apiResource('phone-book', PhoneBookController::class);
    Route::apiResource('sms-purchase', SmsPurchaseController::class);
    Route::get('sms-compose', [SmsController::class, 'index']);
    Route::post('sms-send', [SmsController::class, 'send']);
    Route::match(['get', 'post'], 'send-sms-report', [SmsController::class, 'sentSMSReport']);
    Route::get('sms-get-users', [SmsController::class, 'get_users']);
});
