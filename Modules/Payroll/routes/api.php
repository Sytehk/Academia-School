<?php

use Illuminate\Support\Facades\Route;
use Modules\Payroll\Http\Controllers\API\PayrollController;
use Modules\Payroll\Http\Controllers\API\PayrollReportController;
use Modules\Payroll\Http\Controllers\API\SalaryHeadController;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('salary-heads', SalaryHeadController::class);
    Route::get('/payroll-assign', [PayrollController::class, 'index']);
    Route::post('/payroll-assign', [PayrollController::class, 'create']);
    Route::post('/user-salary-assign', [PayrollController::class, 'userSalaryAssign']);
    Route::get('/payroll-mapping', [PayrollController::class, 'mapping']);
    Route::post('/payroll-mapping', [PayrollController::class, 'mappingStore']);
    Route::get('/salary-create', [PayrollController::class, 'salaryCreateGet']);
    Route::post('/salary-create-store', [PayrollController::class, 'salaryCreate']);
    Route::get('/salary-payment-process', [PayrollController::class, 'salaryPaymentProcess']);
    Route::post('/salary-payment-create', [PayrollController::class, 'salaryPayment']);
    Route::get('/advance-salary-payment', [PayrollController::class, 'advanceSalaryPayment']);
    Route::post('/advance-salary-payment', [PayrollController::class, 'advanceSalaryPay']);
    Route::get('/due-salary-payment', [PayrollController::class, 'dueSalaryPayment']);
    Route::post('/due-salary-payment', [PayrollController::class, 'dueSalaryPay']);
    Route::get('/return-salary-payment', [PayrollController::class, 'returnSalaryPayment']);
    Route::post('/return-salary-payment', [PayrollController::class, 'returnSalaryPay']);
    Route::get('/staff-salary-config', [PayrollController::class, 'staffSalaryConfig']);
    Route::post('/staff-salary-config', [PayrollController::class, 'staffSalaryConfigCreate']);
    Route::get('/salary-statement', [PayrollReportController::class, 'salaryStatementReport']);
    Route::get('/payment-info', [PayrollReportController::class, 'getPaymentInfo']);
});
