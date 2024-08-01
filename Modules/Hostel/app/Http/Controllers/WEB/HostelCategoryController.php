<?php

namespace Modules\Hostel\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Hostel\Models\Hostel;
use Modules\Hostel\Models\HostelCategory;

class HostelCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = HostelCategory::with('hostel')
            ->orderByDesc('id');

        if ($request->filled('hostel_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('hostel_id', $request->hostel_id)->orWhereNull('hostel_id');
            });
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {

                $q->where('standard', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%")
                    ->orWhereHas('hostel', function ($q) use ($search) {
                        $q->where('hostel_name', 'like', "%{$search}%");
                    });
            });
        }

        $categories = $query->paginate(20);

        return view('hostel::hostel_categories.index', compact('categories'));
    }

    public function create()
    {
        $hostels = Hostel::where('institute_id', get_institute_id())
            ->get();

        return view('hostel::hostel_categories.create', compact('hostels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'hostel_id' => 'nullable|exists:hostels,id',
            'standard' => 'required',
            'hostel_fee' => 'required|numeric',
            'note' => 'nullable',
        ]);

        HostelCategory::create([
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'hostel_id' => $request->hostel_id ?: null,
            'standard' => $request->standard,
            'hostel_fee' => $request->hostel_fee,
            'note' => $request->note,

        ]);

        return redirect()
            ->route('hostel-categories.index')
            ->with('success', 'Hostel Category created successfully.');
    }

    public function edit($id)
    {
        $category = HostelCategory::findOrFail($id);

        $hostels = Hostel::where('institute_id', get_institute_id())
            ->get();

        return view(
            'hostel::hostel_categories.edit',
            compact('category', 'hostels')
        );
    }

    public function update(Request $request, $id)
    {

        $request->validate([
            'hostel_id' => 'nullable|exists:hostels,id',
            'standard' => 'required',
            'hostel_fee' => 'required|numeric',
            'note' => 'nullable',
        ]);

        $category = HostelCategory::findOrFail($id);

        $category->update([

            'hostel_id' => $request->hostel_id ?: null,
            'standard' => $request->standard,
            'hostel_fee' => $request->hostel_fee,
            'note' => $request->note,

        ]);

        return redirect()
            ->route('hostel-categories.index')
            ->with('success', 'Hostel Category updated successfully.');
    }

    public function destroy($id)
    {

        $category = HostelCategory::findOrFail($id);

        $category->delete();

        return redirect()
            ->route('hostel-categories.index')
            ->with('success', 'Hostel Category deleted successfully.');
    }
}
