<?php

use Illuminate\Support\Facades\Route;
use Modules\Accounting\Http\Controllers\API\AccountingCategoriesController;
use Modules\Accounting\Http\Controllers\API\AccountingFundsController;
use Modules\Accounting\Http\Controllers\API\AccountingGroupsController;
use Modules\Accounting\Http\Controllers\API\AccountingLedgersController;
use Modules\Accounting\Http\Controllers\API\AccountReportController;
use Modules\Accounting\Http\Controllers\API\APIAccountManagementController;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('accounting-funds', AccountingFundsController::class);
    Route::apiResource('accounting-categories', AccountingCategoriesController::class);
    Route::apiResource('accounting-groups', AccountingGroupsController::class);
    Route::apiResource('accounting-ledgers', AccountingLedgersController::class);

    Route::get('ledger-account-balance', [APIAccountManagementController::class, 'getLedgerAccountBalance']);
    Route::get('chart-of-accounts', [APIAccountManagementController::class, 'cartOfAccounts']);
    Route::post('account-transactions', [APIAccountManagementController::class, 'accountTransaction']);
    Route::post('account-contra-transfer', [APIAccountManagementController::class, 'accountContraTransfer']);
    Route::post('account-journal-transfer', [APIAccountManagementController::class, 'accountJournalTransfer']);
    Route::post('account-fund-transfer', [APIAccountManagementController::class, 'accountFundTransfer']);

    // Accounting Reports
    Route::get('report/user-wise', [AccountReportController::class, 'userWise']);
    Route::get('report/ledger-wise', [AccountReportController::class, 'ledgerWise']);
    Route::get('report/fund-wise', [AccountReportController::class, 'fundWise']);
    Route::get('report/fund-summary', [AccountReportController::class, 'fundSummary']);
    Route::get('report/fund-summary-monthly', [AccountReportController::class, 'fundSummeryMonthly']);

    // Accounting Core Reports
    Route::get('report/balance-sheet', [AccountReportController::class, 'getBalanceSheet']);
    Route::get('report/balance-sheet-details', [AccountReportController::class, 'getBalanceSheetDetails']);
    Route::get('report/trial-balance', [AccountReportController::class, 'getTrialBalance']);
    Route::get('report/trial-balance-details', [AccountReportController::class, 'getBalanceSheetDetails']);
    Route::get('report/cash-flow-statement', [AccountReportController::class, 'getCashFlowStatement']);
    Route::get('report/cash-book-account', [AccountReportController::class, 'getCashBookAccount']);
    Route::get('report/voucher-wise', [AccountReportController::class, 'getVoucherWise']);
    Route::get('report/journal-wise', [AccountReportController::class, 'journalWise']);
    Route::get('report/cash-flow-statement-monthly', [AccountReportController::class, 'getCashFlowStatementMonthly']);
    Route::get('report/get-monthly-fee-collections', [AccountReportController::class, 'getMonthlyFeeCollections']);
    Route::get('report/ledger-book-account', [AccountReportController::class, 'getLedgerBookAccount']);
    Route::get('report/cash-flow-details', [AccountReportController::class, 'getCashFlowDetails']);
    Route::get('report/income-statement', [AccountReportController::class, 'getIncomeStatement']);
    Route::get('report/income-statement-details', [AccountReportController::class, 'getIncomeStatementDetails']);
    Route::get('report/cash-summary', [AccountReportController::class, 'getCashSummary']);
});
