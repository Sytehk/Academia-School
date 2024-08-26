<?php

use Illuminate\Support\Facades\Route;
use Modules\Accounting\Http\Controllers\WEB\AccountContraTransferController;
use Modules\Accounting\Http\Controllers\WEB\AccountFundTransferController;
use Modules\Accounting\Http\Controllers\WEB\AccountingCategoriesController;
use Modules\Accounting\Http\Controllers\WEB\AccountingCoreReportController;
use Modules\Accounting\Http\Controllers\WEB\AccountingFundsController;
use Modules\Accounting\Http\Controllers\WEB\AccountingGroupsController;
use Modules\Accounting\Http\Controllers\WEB\AccountingLedgersController;
use Modules\Accounting\Http\Controllers\WEB\AccountJournalTransactionsController;
use Modules\Accounting\Http\Controllers\WEB\AccountTransactionsController;
use Modules\Accounting\Http\Controllers\WEB\ChartOfAccountsController;

Route::group(['middleware' => ['auth']], function () {
    // Accounting related Controllers
    Route::resource('accounting-ledgers', AccountingLedgersController::class);
    Route::resource('accounting-categories', AccountingCategoriesController::class);
    Route::resource('accounting-groups', AccountingGroupsController::class);
    Route::get('/get-account-groups/{id}', [AccountingGroupsController::class, 'getAccountGroups']);
    Route::resource('accounting-funds', AccountingFundsController::class);
    Route::resource('account-transaction-payment', AccountTransactionsController::class);
    Route::get('ledger-account-balance', [AccountTransactionsController::class, 'getLedgerAccountBalance']);
    Route::resource('journal-transactions', AccountJournalTransactionsController::class);
    Route::resource('account-fund-transfers', AccountFundTransferController::class);
    Route::get('get-fund-balance', [AccountFundTransferController::class, 'getFundBalance']);
    Route::resource('account-contra-transfers', AccountContraTransferController::class);
    Route::get('chart-of-accounts', [ChartOfAccountsController::class, 'index'])->name('chart-of-accounts.index');

    // Accounting Core Reports
    Route::get('balance-sheet', [AccountingCoreReportController::class, 'getBalanceSheet'])->name('balance-sheet');
    Route::get('balance-sheet-details', [AccountingCoreReportController::class, 'getBalanceSheetDetails'])->name('balance-sheet-details');
    Route::get('trial-balance', [AccountingCoreReportController::class, 'getTrialBalance'])->name('trial-balance');
    Route::get('trial-balance-details', [AccountingCoreReportController::class, 'getBalanceSheetDetails'])->name('trial-balance-details');
    Route::get('cash-flow-statement', [AccountingCoreReportController::class, 'getCashFlowStatement'])->name('cash-flow-statement');
    Route::get('cash-flow-details', [AccountingCoreReportController::class, 'getCashFlowDetails'])->name('cash-flow-details');
    Route::get('cash-book-account', [AccountingCoreReportController::class, 'getCashBookAccount'])->name('cash-book-account');
    Route::get('ledger-book-account', [AccountingCoreReportController::class, 'getLedgerBookAccount'])->name('ledger-book-account');
    Route::get('income-statement', [AccountingCoreReportController::class, 'getIncomeStatement'])->name('income-statement');
    Route::get('income-statement-details', [AccountingCoreReportController::class, 'getIncomeStatementDetails'])->name('income-statement-details');
    Route::get('cash-summary', [AccountingCoreReportController::class, 'getCashSummary'])->name('cash-summary');
});
