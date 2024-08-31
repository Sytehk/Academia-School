<?php

namespace Modules\Accounting\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\AccountTransaction;
use Modules\Accounting\Models\AccountTransactionDetail;

class AccountReportController extends Controller
{
    public function getBalanceSheet(Request $request): JsonResponse
    {
        $startDate = $request->from_date ?? null;
        $endDate = $request->to_date ?? null;
        $ledgers = null;

        $ledgers = AccountTransactionDetail::select([
            'account_transaction_details.ledger_id',
            'accounting_ledgers.ledger_name',
            DB::raw('SUM(account_transaction_details.debit) AS total_debit'),
            DB::raw('SUM(account_transaction_details.credit) AS total_credit'),
        ])
            ->join('accounting_ledgers', 'account_transaction_details.ledger_id', '=', 'accounting_ledgers.id')
            ->where('account_transaction_details.institute_id', get_institute_id())
            ->where('account_transaction_details.branch_id', get_branch_id())
            ->whereBetween('account_transaction_details.transaction_date', [$startDate, $endDate])
            ->groupBy('account_transaction_details.ledger_id', 'accounting_ledgers.ledger_name')->get();

        return $this->responseSuccess(
            $ledgers,
            _lang('Balance Sheet have been fetched successfully.')
        );
    }

    public function getBalanceSheetDetails(Request $request): JsonResponse
    {
        $account_details = AccountTransactionDetail::where('ledger_id', $request->ledger_id)->with('accountTransaction')->get();

        return $this->responseSuccess(
            $account_details,
            _lang('Balance Sheet Details have been fetched successfully.')
        );
    }

    public function getTrialBalance(Request $request): JsonResponse
    {
        $startDate = $request->from_date ?? null;
        $endDate = $request->to_date ?? null;
        $ledgers = null;

        $ledgers = AccountTransactionDetail::select([
            'account_transaction_details.ledger_id',
            'accounting_ledgers.ledger_name',
            DB::raw('SUM(account_transaction_details.debit) AS total_debit'),
            DB::raw('SUM(account_transaction_details.credit) AS total_credit'),
        ])
            ->join('accounting_ledgers', 'account_transaction_details.ledger_id', '=', 'accounting_ledgers.id')
            ->where('account_transaction_details.institute_id', get_institute_id())
            ->where('account_transaction_details.branch_id', get_branch_id())
            ->whereBetween('account_transaction_details.transaction_date', [$startDate, $endDate])
            ->groupBy('account_transaction_details.ledger_id', 'accounting_ledgers.ledger_name')->get();

        return $this->responseSuccess(
            $ledgers,
            _lang('Trail Balance have been fetched successfully.')
        );
    }

    public function getCashFlowStatement(Request $request): JsonResponse
    {
        $year = $request->year ?? current_year();
        $monthFilter = $request->month ?? null;

        // List of months from January to December
        $months = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        ];

        // Base query
        $query = DB::table('account_transactions')
            ->join('account_transaction_details', 'account_transactions.id', '=', 'account_transaction_details.account_transactions_id')
            ->whereYear('account_transactions.transaction_date', $year)
            ->where('account_transactions.institute_id', get_institute_id())
            ->where('account_transactions.branch_id', get_branch_id());

        // Apply month filter if provided
        if ($monthFilter && in_array($monthFilter, $months)) {
            $query->whereMonth('account_transactions.transaction_date', date('m', strtotime($monthFilter)));
            // Reduce months list to only the filtered month
            $months = [$monthFilter];
        }

        // Fetch cash flow data
        $cashFlowStatement = $query
            ->select(
                DB::raw('YEAR(account_transactions.transaction_date) as year'),
                DB::raw('DATE_FORMAT(account_transactions.transaction_date, "%M") as month'),
                DB::raw('SUM(account_transaction_details.debit) as total_debit'),
                DB::raw('SUM(account_transaction_details.credit) as total_credit')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy(DB::raw('STR_TO_DATE(CONCAT("1 ", month, " ", year), "%d %M %Y")'), 'asc')
            ->get()
            ->keyBy('month');

        // Prepare result
        $result = [];
        foreach ($months as $month) {
            $monthData = $cashFlowStatement->get($month);
            $result[] = [
                'year' => $monthData->year ?? $year,
                'month' => $month,
                'total_debit' => $monthData->total_debit ?? 0,
                'total_credit' => $monthData->total_credit ?? 0,
            ];
        }

        return $this->responseSuccess(
            $result,
            _lang('Cash Flow Statement has been fetched successfully.')
        );
    }

    public function getMonthlyFeeCollections(Request $request): JsonResponse
    {
        $year = $request->year;

        // Validate input
        if (! is_numeric($year)) {
            return $this->responseError(_lang('Invalid year input.'));
        }

        // Fetch monthly student collections
        $monthlyCollections = DB::table('student_collections')
            ->join('student_collection_details', 'student_collections.id', '=', 'student_collection_details.student_collection_id')
            ->whereYear('student_collections.invoice_date', $year)
            ->where('student_collections.institute_id', get_institute_id())
            ->where('student_collections.branch_id', get_branch_id())
            ->where('student_collections.session_id', get_academic_year())
            ->select(
                DB::raw('MONTH(student_collections.invoice_date) as month'),
                DB::raw('SUM(student_collection_details.total_paid) as collected'),
                DB::raw('SUM(student_collection_details.total_due) as outstanding')
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get()
            ->keyBy('month');

        // Prepare results for all months (Jan - Dec)
        $months = range(1, 12);
        $result = [];

        foreach ($months as $month) {
            $data = $monthlyCollections->get($month);

            $result[] = [
                'month' => Carbon::create($year, $month, 1)->format('F'),
                'collected' => $data->collected ?? 0,
                'outstanding' => $data->outstanding ?? 0,
            ];
        }

        return $this->responseSuccess(
            $result,
            _lang('Monthly Fee Collections Report fetched successfully.')
        );
    }

    public function getCashFlowStatementMonthly(Request $request): JsonResponse
    {
        $year = (int) $request->year;
        $month = $request->month; // Accept month input (e.g., 'January', 'February', etc.)

        // Validate inputs
        if (! is_numeric($year) || ! is_numeric($month) || $month < 1 || $month > 12) {
            return $this->responseError(_lang('Invalid year or month.'));
        }

        // Fetch cash flow data for the given year and month
        $cashFlowStatement = DB::table('account_transactions')
            ->join('account_transaction_details', 'account_transactions.id', '=', 'account_transaction_details.account_transactions_id')
            ->where('account_transactions.institute_id', get_institute_id())
            ->where('account_transactions.branch_id', get_branch_id())
            ->whereYear('account_transactions.transaction_date', $year)
            ->whereMonth('account_transactions.transaction_date', $month)
            ->select(
                DB::raw('DATE(account_transactions.transaction_date) as date'),
                DB::raw('SUM(account_transaction_details.debit) as total_debit'),
                DB::raw('SUM(account_transaction_details.credit) as total_credit')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy('date'); // Key by date to easily merge

        // Get all dates for the selected month
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $allDates = [];
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            $allDates[] = $date->format('Y-m-d');
        }

        // Prepare the result array with all dates and set 0 if no data exists
        $result = [];
        foreach ($allDates as $date) {
            $dateData = $cashFlowStatement->get($date);

            // If no data exists for the date, set the values to 0
            if ($dateData) {
                $result[] = [
                    'date' => $date,
                    'total_debit' => $dateData->total_debit,
                    'total_credit' => $dateData->total_credit,
                ];
            } else {
                $result[] = [
                    'date' => $date,
                    'total_debit' => 0,
                    'total_credit' => 0,
                ];
            }
        }

        return $this->responseSuccess(
            $result,
            _lang('Cash Flow Statement has been fetched successfully.')
        );
    }

    public function getCashBookAccount(Request $request): JsonResponse
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date',
        ]);

        $startDate = $request->from_date;
        $endDate = $request->to_date;

        $cashBookEntries = DB::table('account_transactions')
            ->join('account_transaction_details', 'account_transactions.id', '=', 'account_transaction_details.account_transactions_id')
            ->leftJoin('payment_methods as payment_method', 'account_transactions.payment_method_id', '=', 'payment_method.id')
            ->leftJoin('accounting_ledgers', 'account_transaction_details.ledger_id', '=', 'accounting_ledgers.id')
            ->select(
                'account_transactions.id',
                'account_transactions.transaction_date',
                'account_transactions.type',
                'reference',
                'description',
                'debit',
                'credit',
                'payment_method.name as payment_method_name',
                'accounting_ledgers.ledger_name'
            )
            ->where('account_transactions.institute_id', get_institute_id())
            ->where('account_transactions.branch_id', get_branch_id())
            ->whereBetween('account_transactions.transaction_date', [$startDate, $endDate])
            ->orderBy('account_transactions.transaction_date')
            ->get();

        return $this->responseSuccess(
            $cashBookEntries,
            _lang('Cash Book Entries have been fetched successfully.')
        );
    }

    public function getLedgerBookAccount(Request $request): JsonResponse
    {
        $startDate = $request->from_date;
        $endDate = $request->to_date;
        $ledger = (int) $request->ledger_id;

        $ledgerBookEntries = DB::table('account_transactions')
            ->join('account_transaction_details', 'account_transactions.id', '=', 'account_transaction_details.account_transactions_id')
            ->leftJoin('accounting_ledgers', 'account_transaction_details.ledger_id', '=', 'accounting_ledgers.id')
            ->select(
                'account_transactions.id',
                'account_transactions.transaction_date', // specify the table for transaction_date
                'account_transactions.type',
                'reference',
                'account_transactions.voucher_id as voucher_id',
                'description',
                'debit',
                'credit',
                'accounting_ledgers.ledger_name'
            )
            ->where('account_transactions.institute_id', get_institute_id())
            ->where('account_transactions.branch_id', get_branch_id())
            ->where('account_transaction_details.ledger_id', $ledger)
            ->whereBetween('account_transactions.transaction_date', [$startDate, $endDate]) // specify the table for transaction_date
            ->orderBy('account_transactions.transaction_date') // specify the table for transaction_date
            ->get();

        return $this->responseSuccess(
            $ledgerBookEntries,
            _lang('Ledger Book Entries have been fetched successfully.')
        );
    }

    public function getCashFlowDetails(Request $request): JsonResponse
    {
        $year = $request->year;
        $month = date('n', strtotime($request->month));

        $accountTransactionDetails = AccountTransactionDetail::with([
            'transaction',         // Relation to AccountTransaction
            'fund',                // Fund from fund_id
            'fundTo',              // Fund from fund_to_id
            'ledger',              // Ledger from ledger_id
            'paymentMethod',       // Payment method from payment_method_id
            'paymentMethodTo',      // Payment method from payment_method_to_id
        ])
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->whereHas('transaction', function ($q) use ($year, $month) {
                $q->whereYear('transaction_date', $year)
                    ->whereMonth('transaction_date', $month);
            })
            ->get();

        return $this->responseSuccess(
            $accountTransactionDetails,
            _lang('Account Transaction Details have been fetched successfully.')
        );
    }

    public function getIncomeStatement(Request $request): JsonResponse
    {
        $fromDate = $request['from_date'] ?? date('Y-m-d');
        $toDate = $request['to_date'] ?? date('Y-m-d');
        $ledgerId = $request['ledger_id'] ?? null;
        $data = [];

        $query = AccountTransactionDetail::select([
            'account_transaction_details.ledger_id',
            DB::raw('MIN(account_transaction_details.account_transactions_id) AS account_transactions_id'),
            'accounting_ledgers.ledger_name',
            DB::raw('SUM(account_transaction_details.debit) AS total_debit'),
            DB::raw('SUM(account_transaction_details.credit) AS total_credit'),
        ])
            ->join('accounting_ledgers', 'account_transaction_details.ledger_id', '=', 'accounting_ledgers.id')
            ->where('account_transaction_details.institute_id', get_institute_id())
            ->where('account_transaction_details.branch_id', get_branch_id())
            ->whereBetween('account_transaction_details.transaction_date', [$fromDate, $toDate])
            ->groupBy('account_transaction_details.ledger_id', 'accounting_ledgers.ledger_name');

        // Apply additional filter if `ledger_id` is provided
        if (! empty($ledgerId)) {
            $query->where('account_transaction_details.ledger_id', $ledgerId);
        }

        $ledgers = $query->get();

        // Separate income and expenses based on their values
        $income = $ledgers->filter(function ($ledger) {
            return floatval($ledger->total_credit) > 0 && floatval($ledger->total_debit) == 0;
        });

        $expense = $ledgers->filter(function ($ledger) {
            return floatval($ledger->total_debit) > 0 && floatval($ledger->total_credit) == 0;
        });

        $data['ledgers'] = $ledgers;
        $data['income'] = $income->values();
        $data['expense'] = $expense->values();

        return $this->responseSuccess(
            $data,
            _lang('Income Statement has been fetched successfully.')
        );
    }

    public function getIncomeStatementDetails(Request $request): JsonResponse
    {
        $ledgerId = $request['ledger_id'];
        $account_details = AccountTransactionDetail::where('ledger_id', $ledgerId)
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->with('accountTransaction')->get();

        return $this->responseSuccess(
            $account_details,
            _lang('Income Statement Detail have been fetched successfully.')
        );
    }

    public function getCashSummary(Request $request): JsonResponse
    {
        $fromDate = $request['from_date'] ?? date('Y-m-d');

        $toDate = $request['to_date'] ?? date('Y-m-d');
        $ledgerId = $request['ledger_id'] ?? null;

        $query = AccountTransactionDetail::select([
            'account_transaction_details.ledger_id',
            'accounting_ledgers.ledger_name',
            DB::raw('SUM(account_transaction_details.debit) AS total_debit'),
            DB::raw('SUM(account_transaction_details.credit) AS total_credit'),
        ])
            ->join('accounting_ledgers', 'account_transaction_details.ledger_id', '=', 'accounting_ledgers.id')
            ->where('account_transaction_details.institute_id', get_institute_id())
            ->where('account_transaction_details.branch_id', get_branch_id())
            ->whereBetween('account_transaction_details.created_at', [$fromDate, $toDate])
            ->groupBy('account_transaction_details.ledger_id', 'accounting_ledgers.ledger_name');

        if (! empty($ledgerId)) {
            $query->where('account_transaction_details.ledger_id', $ledgerId);
        }

        $ledgers = $query->get();
        $income = $ledgers->where('total_debit', '0.00')->all();
        $expense = $ledgers->where('total_credit', '0.00')->all();

        $data['ledgers'] = $ledgers;
        $data['income'] = $income;
        $data['expense'] = $expense;

        return $this->responseSuccess(
            $data,
            _lang('Cash Summery have been fetched successfully.')
        );
    }

    public function userWise(Request $request): JsonResponse
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        $transactions = AccountTransaction::query()
            ->select('account_transactions.*', 'account_transaction_details.*', 'accounting_ledgers.ledger_name')
            ->leftJoin('account_transaction_details', 'account_transaction_details.account_transactions_id', '=', 'account_transactions.id')
            ->leftJoin('accounting_ledgers', 'accounting_ledgers.id', '=', 'account_transaction_details.ledger_id')
            ->where('account_transactions.institute_id', get_institute_id())
            ->where('account_transactions.branch_id', get_branch_id())
            ->whereBetween('account_transactions.transaction_date', [$fromDate, $toDate])
            ->where('account_transactions.created_by', Auth::id())
            ->orderBy('account_transactions.id', 'DESC')->get();

        return $this->responseSuccess(
            $transactions,
            _lang('User wise Transactions have been fetched successfully.')
        );
    }

    public function ledgerWise(Request $request): JsonResponse
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        $transactions = AccountTransaction::query()
            ->select('account_transactions.*', 'account_transaction_details.*', 'accounting_ledgers.ledger_name')
            ->leftJoin('account_transaction_details', 'account_transaction_details.account_transactions_id', '=', 'account_transactions.id')
            ->leftJoin('accounting_ledgers', 'accounting_ledgers.id', '=', 'account_transaction_details.ledger_id')
            ->whereBetween('account_transactions.transaction_date', [$fromDate, $toDate])
            ->where('account_transactions.institute_id', get_institute_id())
            ->where('account_transactions.branch_id', get_branch_id())
            ->orderBy('account_transactions.id', 'ASC')->get();

        return $this->responseSuccess(
            $transactions,
            _lang('Ledger wise Transactions have been fetched successfully.')
        );
    }

    public function fundWise(Request $request): JsonResponse
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        $transactions = AccountTransaction::query()
            ->select('account_transactions.*', 'account_transaction_details.*', 'accounting_funds.name as fund_name')
            ->leftJoin('account_transaction_details', 'account_transaction_details.account_transactions_id', '=', 'account_transactions.id')
            ->leftJoin('accounting_funds', 'accounting_funds.id', '=', 'account_transaction_details.fund_id')
            ->whereBetween('account_transactions.transaction_date', [$fromDate, $toDate])
            ->where('account_transactions.institute_id', get_institute_id())
            ->where('account_transactions.branch_id', get_branch_id())
            ->orderBy('account_transactions.id', 'ASC')->get();

        return $this->responseSuccess(
            $transactions,
            _lang('Fund wise Transactions have been fetched successfully.')
        );
    }

    public function fundSummary(Request $request): JsonResponse
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        $transactions = AccountTransaction::query()
            ->select('account_transactions.*', 'account_transaction_details.*', 'accounting_funds.name as fund_name')
            ->leftJoin('account_transaction_details', 'account_transaction_details.account_transactions_id', '=', 'account_transactions.id')
            ->leftJoin('accounting_funds', 'accounting_funds.id', '=', 'account_transaction_details.fund_id')
            ->whereBetween('account_transactions.transaction_date', [$fromDate, $toDate])
            ->where('account_transactions.institute_id', get_institute_id())
            ->where('account_transactions.branch_id', get_branch_id())
            ->orderBy('account_transactions.id', 'ASC')->get();

        return $this->responseSuccess(
            $transactions,
            _lang('Fund Summary Transactions have been fetched successfully.')
        );
    }

    public function fundSummeryMonthly(Request $request): JsonResponse
    {
        $year = $request->year;

        $transactions = AccountTransaction::query()
            ->select('account_transactions.*', 'account_transaction_details.*', 'accounting_funds.name as fund_name')
            ->leftJoin('account_transaction_details', 'account_transaction_details.account_transactions_id', '=', 'account_transactions.id')
            ->leftJoin('accounting_funds', 'accounting_funds.id', '=', 'account_transaction_details.fund_id')
            ->whereYear('account_transactions.transaction_date', '=', $year)
            ->where('account_transactions.institute_id', get_institute_id())
            ->where('account_transactions.branch_id', get_branch_id())
            ->orderBy('account_transactions.id', 'ASC')
            ->get();

        return $this->responseSuccess(
            $transactions,
            _lang('Fund Monthly Transactions have been fetched successfully.')
        );
    }

    public function getVoucherWise(Request $request): JsonResponse
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;
        $type = $request->type;

        $query = AccountTransaction::query()
            ->select('account_transactions.*', 'account_transaction_details.*', 'accounting_ledgers.ledger_name')
            ->join('account_transaction_details', 'account_transaction_details.account_transactions_id', '=', 'account_transactions.id')
            ->leftJoin('accounting_ledgers', 'accounting_ledgers.id', '=', 'account_transaction_details.ledger_id')
            ->where('account_transactions.institute_id', get_institute_id())
            ->where('account_transactions.branch_id', get_branch_id())
            ->whereBetween('account_transactions.transaction_date', [$fromDate, $toDate]);
        if (
            $type !== 'All'
        ) {
            $query->where('account_transactions.type', $type);
        }

        $transactions = $query->orderBy('account_transactions.id', 'DESC')->get();

        return $this->responseSuccess(
            $transactions,
            _lang('Voucher Wise Transactions have been fetched successfully.')
        );
    }

    public function journalWise(Request $request): JsonResponse
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;
        $type = 'journal';

        $transactions = AccountTransaction::query()
            ->select('account_transactions.*', 'account_transaction_details.*', 'accounting_ledgers.ledger_name')
            ->join('account_transaction_details', 'account_transaction_details.account_transactions_id', '=', 'account_transactions.id')
            ->leftJoin('accounting_ledgers', 'accounting_ledgers.id', '=', 'account_transaction_details.ledger_id')
            ->where('account_transactions.type', $type)
            ->where('account_transactions.institute_id', get_institute_id())
            ->where('account_transactions.branch_id', get_branch_id())
            ->whereBetween('account_transactions.transaction_date', [$fromDate, $toDate])
            ->orderBy('account_transactions.id', 'ASC')
            ->get();

        return $this->responseSuccess(
            $transactions,
            _lang('Journal Wise Transactions have been fetched successfully.')
        );
    }
}
