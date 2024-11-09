<?php

namespace Modules\Inventory\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Inventory\Repositories\InventoryCategoryRepository;
use RealRashid\SweetAlert\Facades\Alert;

class CategoryController extends Controller
{
    public function __construct(private InventoryCategoryRepository $categories) {}

    public function index(Request $request)
    {
        $categories = $this->categories->paginate(20, $request->only('search'));

        return view('inventory::categories.index', compact('categories'));
    }

    public function create()
    {
        return view('inventory::categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'note' => 'nullable|string',
        ]);

        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        $this->categories->create($data);

        Alert::toast(_lang('Category created successfully.'), 'success');

        return redirect()->route('inventory-categories.index');
    }

    public function edit(int $id)
    {
        $category = $this->categories->show($id);

        if (! $category) {
            Alert::error('Error', _lang('Category not found.'));

            return redirect()->route('inventory-categories.index');
        }

        return view('inventory::categories.edit', compact('category'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'note' => 'nullable|string',
        ]);

        $this->categories->update($data, $id);

        Alert::toast(_lang('Category updated successfully.'), 'success');

        return redirect()->route('inventory-categories.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->categories->delete($id);

        Alert::toast(_lang('Category deleted successfully.'), 'success');

        return redirect()->route('inventory-categories.index');
    }
}
