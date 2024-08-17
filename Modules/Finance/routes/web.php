<?php

use Illuminate\Support\Facades\Route;
use Modules\Finance\Http\Controllers\WEB\AbsentFineController;
use Modules\Finance\Http\Controllers\WEB\AmountConfigController;
use Modules\Finance\Http\Controllers\WEB\DateConfigController;
use Modules\Finance\Http\Controllers\WEB\FeeDateConfigController;
use Modules\Finance\Http\Controllers\WEB\FeeHeadController;
use Modules\Finance\Http\Controllers\WEB\FeeManagementReportController;
use Modules\Finance\Http\Controllers\WEB\FeesController;
use Modules\Finance\Http\Controllers\WEB\FeesMappingController;
use Modules\Finance\Http\Controllers\WEB\FeeSubHeadController;
use Modules\Finance\Http\Controllers\WEB\QuickCollectionController;
use Modules\Finance\Http\Controllers\WEB\WaiverConfigController;
use Modules\Finance\Http\Controllers\WEB\WaiversController;

// Fees Management
Route::group(['middleware' => ['auth']], function () {
    Route::resource('fee-head', FeeHeadController::class);
    Route::post('fee-head-serial-update', [FeeHeadController::class, 'serialUpdate'])->name('fee-head-serial.update');
    Route::get('fee-head-delete/{id}', [FeeHeadController::class, 'feeHeadDelete'])->name('fee-head.delete');
    Route::resource('fee-sub-head', FeeSubHeadController::class);
    Route::post('fee-sub-head-serial-update', [FeeSubHeadController::class, 'serialFeeSubHeadUpdate'])->name('fee-sub-head-serial.update');
    Route::get('fee-sub-head-delete/{id}', [FeeSubHeadController::class, 'feeSubHeadDelete'])->name('fee-sub-head.delete');
    Route::resource('fees-mapping', FeesMappingController::class);
    Route::resource('amount-config', AmountConfigController::class);
    Route::resource('fees', FeesController::class);
    Route::resource('date-config', DateConfigController::class);
    Route::resource('fee-date-config', FeeDateConfigController::class);
    Route::resource('absent-fines', AbsentFineController::class);

    Route::resource('waiver-config', WaiverConfigController::class);
    Route::get('attendance-waiver', [WaiverConfigController::class, 'attendanceWaiver'])->name('attendance-waiver.index');
    Route::post('attendance-waiver', [WaiverConfigController::class, 'attendanceWaiverStore'])->name('attendance-waiver.store');
    Route::resource('quick-collection', QuickCollectionController::class);
    Route::get('quick-collection-student', [QuickCollectionController::class, 'search'])->name('quick-collection-student');
    Route::get('get-attendance-fine-amount/{student_id}', [QuickCollectionController::class, 'getAttendanceFineAmount'])->name('get-attendance-fine-amount');
    Route::get('get-quiz-fine-amount/{student_id}', [QuickCollectionController::class, 'getQuizFineAmount'])->name('get-quiz-fine-amount');
    Route::get('get-lab-fine-amount/{student_id}', [QuickCollectionController::class, 'getLabFineAmount'])->name('get-lab-fine-amount');
    Route::get('get-tc-amount', [QuickCollectionController::class, 'getTcAmount'])->name('get-tc-amount');
    Route::post('student-collection/sub-head-wise-calculation', [QuickCollectionController::class, 'getCollectionAmounts'])->name('student-collection.sub-head-wise-calculation');
    Route::get('student-collection/invoice/{id}', [QuickCollectionController::class, 'invoice'])->name('student-collection.invoice');
    Route::post('waiver-serial-update', [WaiversController::class, 'serialWaiverUpdate'])->name('waiver-serial.update');
    Route::get('waiver-delete/{id}', [WaiversController::class, 'waiverDelete'])->name('waivers.delete');
    Route::resource('waivers', WaiversController::class);
    Route::match(['get', 'post'], 'unpaid-info', [FeeManagementReportController::class, 'getUnpaidFeeInfo'])->name('unpaid-info');

    // Paid Info and Report
    Route::get('payment-fee-info', [FeeManagementReportController::class, 'getPaymentInfo'])->name('payment-fee-info');
    Route::get('payment-fee-info-print', [FeeManagementReportController::class, 'printPaymentInfo'])->name('payment-fee-info-print');
    Route::get('payment-fee-info-pdf', [FeeManagementReportController::class, 'downloadPaymentInfoPdf'])->name('payment-fee-info-pdf');
});
