<?php

use Illuminate\Support\Facades\Route;
use Modules\Payroll\Http\Controllers\WEB\PayrollController;
use Modules\Payroll\Http\Controllers\WEB\PayrollReportController;
use Modules\Payroll\Http\Controllers\WEB\SalaryHeadController;

Route::group(['middleware' => ['auth']], function () {
    // PayrollController Route
    Route::get('/payroll-assign', [PayrollController::class, 'index'])->name('payroll_assign.index');
    Route::post('/payroll-create', [PayrollController::class, 'create'])->name('payroll_assign.create');
    Route::get('/payroll-mapping', [PayrollController::class, 'mapping'])->name('payroll_mapping.create');
    Route::post('/payroll-mapping', [PayrollController::class, 'mappingStore'])->name('payroll_mapping.store');
    Route::get('/salary-create', [PayrollController::class, 'salaryCreateGet'])->name('salary.create.index');
    Route::post('/salary-create', [PayrollController::class, 'salaryCreateGet'])->name('salary.create.search');
    Route::post('/salary-create-store', [PayrollController::class, 'salaryCreate'])->name('salary.create.store');
    Route::get('/salary-payment-process', [PayrollController::class, 'salaryPaymentProcess'])->name('salary.payment-process.index');
    Route::post('/salary-payment-process', [PayrollController::class, 'salaryPaymentProcess'])->name('salary.payment-process.search');
    Route::post('/salary-payment-create', [PayrollController::class, 'salaryPayment'])->name('salary.payment-process.create');
    Route::get('/advance-salary-payment', [PayrollController::class, 'advanceSalaryPayment'])->name('advance.salary.payment');
    Route::post('/advance-salary-payment', [PayrollController::class, 'advanceSalaryPayment'])->name('advance.salary.payment.check');
    Route::post('/advance-salary-pay', [PayrollController::class, 'advanceSalaryPay'])->name('advance.salary.pay');
    Route::get('/due-salary-payment', [PayrollController::class, 'dueSalaryPayment'])->name('due.salary.payment');
    Route::post('/due-salary-payment', [PayrollController::class, 'dueSalaryPayment'])->name('due.salary.payment.check');
    Route::post('/due-salary-pay', [PayrollController::class, 'dueSalaryPay'])->name('due.salary.pay');
    Route::get('/return-salary-payment', [PayrollController::class, 'returnSalaryPayment'])->name('return.salary.payment');
    Route::post('/return-salary-payment', [PayrollController::class, 'returnSalaryPayment'])->name('return.salary.payment.check');
    Route::post('/return-salary-pay', [PayrollController::class, 'returnSalaryPay'])->name('return.salary.pay');
    Route::resource('salary-heads', SalaryHeadController::class);
    Route::get('/staff-salary-config', [PayrollController::class, 'staffSalaryConfig'])->name('staff-salary-config.index');
    Route::post('/staff-salary-config', [PayrollController::class, 'staffSalaryConfigCreate'])->name('staff-salary-config.store');
    Route::get('/salary-statement', [PayrollReportController::class, 'salaryStatementReport'])->name('salary-statement-report');
    Route::get('/payment-info', [PayrollReportController::class, 'getPaymentInfo'])->name('payment-info-report');
});
