<?php

namespace Modules\Inventory\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Academic\Models\Student;
use Modules\Accounting\Models\AccountingLedger;
use Modules\Inventory\Exceptions\InventorySaleException;
use Modules\Inventory\Models\InventoryItem;
use Modules\Inventory\Models\InventorySale;
use Modules\Inventory\Services\InventorySaleService;
use RealRashid\SweetAlert\Facades\Alert;
use Throwable;

class SaleController extends Controller
{
    public function __construct(private InventorySaleService $saleService) {}

    public function index(Request $request)
    {
        $sales = InventorySale::with('student.user', 'items.item')
            ->orderByDesc('id')
            ->paginate(20);

        return view('inventory::sales.index', compact('sales'));
    }

    public function create(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $students = collect();

        if ($search !== '') {
            $students = Student::query()
                ->select('students.id', 'users.name', 'student_sessions.roll', 'classes.class_name', 'sections.section_name')
                ->join('users', 'users.id', '=', 'students.user_id')
                ->join('student_sessions', function ($join) {
                    $join->on('students.id', '=', 'student_sessions.student_id')
                        ->where('student_sessions.session_id', get_option('academic_year'));
                })
                ->leftJoin('classes', 'classes.id', '=', 'student_sessions.class_id')
                ->leftJoin('sections', 'sections.id', '=', 'student_sessions.section_id')
                ->where('students.institute_id', get_institute_id())
                ->where('students.branch_id', get_branch_id())
                ->where(function ($q) use ($search) {
                    $q->where('users.name', 'like', "%{$search}%")
                        ->orWhere('student_sessions.roll', 'like', "%{$search}%");
                })
                ->limit(20)
                ->get();
        }

        $items = InventoryItem::with('category')->where('status', 1)->where('stock_quantity', '>', 0)->orderBy('name')->get();
        $ledgers = AccountingLedger::instituteBranch()->orderBy('ledger_name')->get();

        return view('inventory::sales.create', compact('students', 'items', 'ledgers', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'receive_ledger_id' => 'nullable|integer|exists:accounting_ledgers,id',
            'note' => 'nullable|string|max:1000',
            'paid_now' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:inventory_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $sale = $this->saleService->createSale($data);
        } catch (InventorySaleException $e) {
            Alert::error('Error', $e->getMessage());

            return back()->withInput();
        } catch (Throwable $e) {
            Alert::error('Error', _lang('Something went wrong while recording the sale.'));

            return back()->withInput();
        }

        Alert::toast(_lang('Sale recorded successfully.'), 'success');

        return redirect()->route('inventory-sales.show', $sale->id);
    }

    public function show(int $id)
    {
        $sale = InventorySale::with('student.user', 'items.item', 'creator')->findOrFail($id);

        return view('inventory::sales.show', compact('sale'));
    }
}
