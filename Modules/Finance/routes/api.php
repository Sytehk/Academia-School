<?php

use Illuminate\Support\Facades\Route;
use Modules\Finance\Http\Controllers\API\AbsentFineController;
use Modules\Finance\Http\Controllers\API\APIQuickCollectionController;
use Modules\Finance\Http\Controllers\API\FeeDateConfigController;
use Modules\Finance\Http\Controllers\API\FeeHeadController;
use Modules\Finance\Http\Controllers\API\FeeManagementReportController;
use Modules\Finance\Http\Controllers\API\FeesController;
use Modules\Finance\Http\Controllers\API\FeesMappingController;
use Modules\Finance\Http\Controllers\API\FeeSubHeadController;
use Modules\Finance\Http\Controllers\API\WaiverConfigController;
use Modules\Finance\Http\Controllers\API\WaiversController;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('fee-head', FeeHeadController::class);
    Route::delete('fee-head-delete/{id}', [FeeHeadController::class, 'feeHeadDelete']);

    Route::apiResource('fee-sub-head', FeeSubHeadController::class);
    Route::delete('fee-sub-head-delete/{id}', [FeeSubHeadController::class, 'feeSubHeadDelete']);
    Route::apiResource('waivers', WaiversController::class);

    Route::apiResource('fees-mapping', FeesMappingController::class);
    Route::apiResource('fees', FeesController::class);
    Route::post('fee-date-config-search', [FeeDateConfigController::class, 'dateConfig']);
    Route::apiResource('fee-date-config', FeeDateConfigController::class);
    Route::apiResource('absent-fines', AbsentFineController::class);
    Route::apiResource('waiver-config', WaiverConfigController::class);

    Route::apiResource('quick-collection', APIQuickCollectionController::class);
    Route::get('quick-collection-student', [APIQuickCollectionController::class, 'search']);
    Route::get('get-attendance-fine-amount/{student_id}', [APIQuickCollectionController::class, 'getAttendanceFineAmount']);
    Route::get('get-quiz-fine-amount/{student_id}', [APIQuickCollectionController::class, 'getQuizFineAmount']);
    Route::get('get-lab-fine-amount/{student_id}', [APIQuickCollectionController::class, 'getLabFineAmount']);
    Route::get('get-tc-amount', [APIQuickCollectionController::class, 'getTcAmount']);
    Route::post('student-collection-sub-head-wise-calculation', [APIQuickCollectionController::class, 'getCollectionAmounts']);
    Route::get('student-collection-invoice/{id}', [APIQuickCollectionController::class, 'invoice']);
    Route::get('paid-reports', [APIQuickCollectionController::class, 'getPaidReports']);
    Route::get('unpaid-reports', [APIQuickCollectionController::class, 'getUnpaidReports']);

    // Fee Collection Report
    Route::get('monthly-paid-info', [FeeManagementReportController::class, 'getMonthlyPaidInfo']);
    Route::get('class-wise-payment-summary', [FeeManagementReportController::class, 'getClassWisePaymentSummary']);
    Route::post('unpaid-fee-info', [FeeManagementReportController::class, 'getUnpaidFeeInfo']);
    Route::get('payment-ratio-info', [FeeManagementReportController::class, 'getPaymentRatioInfo']);
    Route::get('payment-fee-info', [FeeManagementReportController::class, 'getPaymentInfo']);
    Route::get('head-wise-payment', [FeeManagementReportController::class, 'getHeadWisePayment']);
    Route::get('head-wise-due', [FeeManagementReportController::class, 'getSubHeadWisePayment']);
    Route::get('unpaid-summery', [FeeManagementReportController::class, 'getUnpaidFeeSummery']);
    Route::get('paid-invoice', [FeeManagementReportController::class, 'getPaidFees']);
});
