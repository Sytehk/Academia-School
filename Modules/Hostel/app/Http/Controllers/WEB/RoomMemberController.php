<?php

namespace Modules\Hostel\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Academic\Models\Student;
use Modules\Hostel\Models\Room;
use Modules\Hostel\Models\RoomMember;

class RoomMemberController extends Controller
{
    public function index(Request $request)
    {
        $query = RoomMember::with([
            'student',
            'room',
            'room.hostelCategory',
        ])->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($student) use ($search) {
                    $student->where('first_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })
                    ->orWhereHas('room', function ($room) use ($search) {
                        $room->where('room_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        $members = $query->paginate(10);

        return view('hostel::room_members.index', compact('members'));
    }

    public function create()
    {
        $rooms = Room::with('hostelCategory')->get();

        $students = Student::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->orderBy('first_name')
            ->get();

        return view('hostel::room_members.create', compact(
            'rooms',
            'students'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'student_id' => 'required|exists:students,id',
        ]);

        RoomMember::create([
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'room_id' => $request->room_id,
            'student_id' => $request->student_id,
            'start_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        return redirect()
            ->route('room-members.index')
            ->with('success', _lang('Room member created successfully.'));
    }

    public function profile(int $id)
    {
        $member = RoomMember::with(['student.user', 'room.hostel', 'room.building', 'room.floor', 'bed', 'collections', 'leaves'])
            ->findOrFail($id);

        return view('hostel::room_members.profile', compact('member'));
    }

    public function card(int $id)
    {
        $member = RoomMember::with(['student.user', 'room.hostel', 'bed'])->findOrFail($id);

        return view('hostel::room_members.card', compact('member'));
    }

    public function edit($id)
    {
        $member = RoomMember::findOrFail($id);

        $rooms = Room::with('hostelCategory')->get();

        $students = Student::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->orderBy('first_name')
            ->get();

        return view('hostel::room_members.edit', compact(
            'member',
            'rooms',
            'students'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'student_id' => 'required|exists:students,id',
        ]);

        $member = RoomMember::findOrFail($id);

        $member->update([
            'room_id' => $request->room_id,
            'student_id' => $request->student_id,
        ]);

        return redirect()
            ->route('room-members.index')
            ->with('success', _lang('Room member updated successfully.'));
    }

    public function destroy($id)
    {
        $member = RoomMember::findOrFail($id);

        $member->delete();

        return redirect()
            ->route('room-members.index')
            ->with('success', _lang('Room member deleted successfully.'));
    }
}
