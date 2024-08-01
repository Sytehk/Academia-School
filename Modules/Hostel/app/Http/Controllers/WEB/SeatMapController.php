<?php

namespace Modules\Hostel\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Modules\Hostel\Models\Hostel;
use Modules\Hostel\Models\Room;

class SeatMapController extends Controller
{
    /**
     * Visual bed-occupancy grid, one card per room. Occupancy is read from the
     * active RoomMember relation (Bed::roomMember), not beds.status, since
     * nothing in the module keeps that column in sync on assignment/checkout.
     */
    public function index(): Factory|View
    {
        $rooms = Room::with(['hostelCategory', 'hostel', 'beds.roomMember.student.user'])
            ->orderBy('room_number')
            ->get();

        $hostels = Hostel::all();

        $totalBeds = $rooms->sum(fn ($room) => $room->beds->count());
        $occupiedBeds = $rooms->sum(fn ($room) => $room->beds->filter(fn ($bed) => $bed->roomMember !== null)->count());

        return view('hostel::seat_map.index', compact('rooms', 'hostels', 'totalBeds', 'occupiedBeds'));
    }
}
