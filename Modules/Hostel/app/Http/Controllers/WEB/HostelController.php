<?php

namespace Modules\Hostel\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Hostel\Models\Hostel;

class HostelController extends Controller
{
    public function index(Request $request)
    {

        $query = Hostel::orderByDesc('id');

        if ($search = $request->get('search')) {

            $query->where(function ($q) use ($search) {

                $q->where('hostel_name', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $hostels = $query->paginate(20);

        return view('hostel::hostel.index', compact('hostels'));
    }

    public function create()
    {

        return view(
            'hostel::hostel.create'
        );
    }

    public function store(Request $request)
    {

        $request->validate([

            'hostel_name' => 'required',
            'type' => 'required',
            'address' => 'required',
            'note' => 'nullable',

        ]);

        Hostel::create([

            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),

            'hostel_name' => $request->hostel_name,
            'type' => $request->type,
            'address' => $request->address,
            'note' => $request->note,

        ]);

        return redirect()
            ->route('hostels.index')
            ->with(
                'success',
                'Hostel created successfully.'
            );
    }

    public function edit($id)
    {

        $hostel = Hostel::findOrFail($id);

        return view(
            'hostel::hostel.edit',
            compact('hostel')
        );
    }

    public function update(Request $request, $id)
    {

        $request->validate([

            'hostel_name' => 'required',
            'type' => 'required',
            'address' => 'required',
            'note' => 'nullable',

        ]);

        $hostel = Hostel::findOrFail($id);

        $hostel->update([

            'hostel_name' => $request->hostel_name,
            'type' => $request->type,
            'address' => $request->address,
            'note' => $request->note,

        ]);

        return redirect()
            ->route('hostels.index')
            ->with(
                'success',
                'Hostel updated successfully.'
            );
    }

    public function destroy($id)
    {

        $hostel = Hostel::findOrFail($id);

        $hostel->delete();

        return redirect()
            ->route('hostels.index')
            ->with(
                'success',
                'Hostel deleted successfully.'
            );
    }
}
