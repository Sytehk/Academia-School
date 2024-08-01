<?php

namespace Modules\Hostel\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Hostel\Models\HostelCategory;
use Modules\Hostel\Models\Room;

class RoomController extends Controller
{
    /**
     * Display room list
     */
    public function index(Request $request)
    {
        $query = Room::with('hostelCategory')
            ->orderBy('id', 'DESC');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('room_number', 'LIKE', "%{$search}%")
                    ->orWhere('capacity', 'LIKE', "%{$search}%");
            });
        }

        // Filter by hostel category
        if ($request->filled('hostel_category_id')) {
            $query->where('hostel_category_id', $request->hostel_category_id);
        }

        $rooms = $query->paginate(10);

        return view('hostel::rooms.index', compact('rooms'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $hostelCategories = HostelCategory::orderBy('standard')->get();

        return view('hostel::rooms.create', compact('hostelCategories'));
    }

    /**
     * Store room
     */
    public function store(Request $request)
    {
        $request->validate([
            'hostel_category_id' => 'required|exists:hostel_categories,id',
            'room_number' => 'required',
            'capacity' => 'required|numeric',
        ]);

        Room::create([
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'hostel_category_id' => $request->hostel_category_id,
            'room_number' => $request->room_number,
            'capacity' => $request->capacity,
        ]);

        return redirect()
            ->route('rooms.index')
            ->with('success', _lang('Room created successfully.'));
    }

    /**
     * Show room details
     */
    public function show($id)
    {
        $room = Room::with('hostelCategory')->findOrFail($id);

        return view('hostel::rooms.show', compact('room'));
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $room = Room::findOrFail($id);

        $hostelCategories = HostelCategory::orderBy('name')->get();

        return view('hostel::rooms.edit', compact(
            'room',
            'hostelCategories'
        ));
    }

    /**
     * Update room
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'hostel_category_id' => 'required|exists:hostel_categories,id',
            'room_number' => 'required',
            'capacity' => 'required|numeric',
        ]);

        $room = Room::findOrFail($id);

        $room->update([
            'hostel_category_id' => $request->hostel_category_id,
            'room_number' => $request->room_number,
            'capacity' => $request->capacity,
        ]);

        return redirect()
            ->route('rooms.index')
            ->with('success', _lang('Room updated successfully.'));
    }

    /**
     * Delete room
     */
    public function destroy($id)
    {
        $room = Room::findOrFail($id);

        $room->delete();

        return redirect()
            ->route('rooms.index')
            ->with('success', _lang('Room deleted successfully.'));
    }
}
