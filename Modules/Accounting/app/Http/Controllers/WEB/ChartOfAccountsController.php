<?php

namespace Modules\Accounting\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Modules\Accounting\Models\AccountingCategory;

class ChartOfAccountsController extends Controller
{
    public function index(): Factory|View
    {
        $accountingCategories = AccountingCategory::with('accountingGroups')->select('id', 'name', 'nature', 'type')->get();

        return view('accounting::chart_of_accounts.index', [
            'accountingCategories' => $accountingCategories,
        ]);
    }
}
