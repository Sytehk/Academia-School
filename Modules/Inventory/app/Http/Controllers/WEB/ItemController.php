<?php

namespace Modules\Inventory\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Inventory\Models\InventoryCategory;
use Modules\Inventory\Repositories\InventoryItemRepository;
use RealRashid\SweetAlert\Facades\Alert;

class ItemController extends Controller
{
    public function __construct(private InventoryItemRepository $items) {}

    public function index(Request $request)
    {
        $items = $this->items->paginate(20, $request->only('search', 'category_id'));
        $categories = InventoryCategory::orderBy('name')->get();

        return view('inventory::items.index', compact('items', 'categories'));
    }

    public function create()
    {
        $categories = InventoryCategory::orderBy('name')->get();

        return view('inventory::items.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => 'required|integer|exists:inventory_categories,id',
            'name' => 'required|string|max:150',
            'sku' => 'nullable|string|max:60',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'status' => 'nullable|in:0,1',
        ]);

        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();
        $data['status'] = $data['status'] ?? 1;

        $this->items->create($data);

        Alert::toast(_lang('Item created successfully.'), 'success');

        return redirect()->route('inventory-items.index');
    }

    public function edit(int $id)
    {
        $item = $this->items->show($id);

        if (! $item) {
            Alert::error('Error', _lang('Item not found.'));

            return redirect()->route('inventory-items.index');
        }

        $categories = InventoryCategory::orderBy('name')->get();

        return view('inventory::items.edit', compact('item', 'categories'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => 'required|integer|exists:inventory_categories,id',
            'name' => 'required|string|max:150',
            'sku' => 'nullable|string|max:60',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'status' => 'nullable|in:0,1',
        ]);

        $data['status'] = $data['status'] ?? 1;

        $this->items->update($data, $id);

        Alert::toast(_lang('Item updated successfully.'), 'success');

        return redirect()->route('inventory-items.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->items->delete($id);

        Alert::toast(_lang('Item deleted successfully.'), 'success');

        return redirect()->route('inventory-items.index');
    }
}
