<?php

namespace Modules\Hostel\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Hostel\Models\Room;

class RoomController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Room::query()
            ->with('hostelCategory') // optional: include related data
            ->orderBy('id', 'DESC');

        // 🔍 Search by room number or capacity
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('room_number', 'LIKE', "%{$search}%")
                    ->orWhere('capacity', 'LIKE', "%{$search}%");
            });
        }

        // 🏠 Filter by hostel category
        if ($request->filled('hostel_category_id')) {
            $query->where('hostel_category_id', $request->hostel_category_id);
        }

        // 📄 Pagination (default 10 per page)
        $perPage = $request->get('per_page', 10);
        $rooms = $query->paginate($perPage);

        return $this->responseSuccess($rooms, 'Room fetched successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'hostel_category_id' => 'required|exists:hostel_categories,id',
            'room_number' => 'required',
            'capacity' => 'required|numeric',
        ]);

        $room = new Room;
        $room->institute_id = get_institute_id();
        $room->branch_id = get_branch_id();
        $room->hostel_category_id = $request->hostel_category_id;
        $room->room_number = $request->room_number;
        $room->capacity = $request->capacity;
        $room->save();

        return $this->responseSuccess($room, 'Room create successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $room = Room::find($id);
        if (! $room) {
            return $this->responseError([], _lang('Something went wrong. Room can not be found.'));
        }

        return $this->responseSuccess($room, 'Room create successfully.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'hostel_category_id' => 'required',
            'room_number' => 'required',
            'capacity' => 'required',
        ]);

        $room = Room::find($id);
        $room->hostel_category_id = $request->hostel_category_id;
        $room->room_number = $request->room_number;
        $room->capacity = $request->capacity;
        $room->save();

        return $this->responseSuccess($room, 'Room update successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $room = Room::find($id);
        if (! $room) {
            return $this->responseError([], _lang('Something went wrong. Room can not be found.'));
        }

        $room->delete();

        return $this->responseSuccess([], 'Room delete successfully.');
    }
}
