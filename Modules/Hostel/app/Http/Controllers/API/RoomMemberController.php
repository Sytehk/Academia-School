<?php

namespace Modules\Hostel\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Academic\Models\Student;
use Modules\Hostel\Models\RoomMember;

class RoomMemberController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = RoomMember::with([
            'student:id,first_name,phone',
            'room:id,room_number,hostel_category_id',
            'room.hostelCategory:id,standard',
        ])
            ->select('id', 'student_id', 'room_id', 'created_at')
            ->orderByDesc('id');

        // 🔍 Optional search
        if ($search = trim($request->get('search'))) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('first_name', 'like', "{$search}%")
                    ->orWhere('phone', 'like', "{$search}%");
            })->orWhereHas('room', function ($q) use ($search) {
                $q->where('room_number', 'like', "{$search}%");
            });
        }

        // Optional filter by room ID
        if ($roomId = $request->get('room_id')) {
            $query->where('room_id', $roomId);
        }

        // 📄 Pagination (default 20 per page)
        $perPage = (int) $request->get('per_page', 20);
        $members = $query->paginate($perPage);

        return $this->responseSuccess($members, 'Room members fetched successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'student_id' => 'required|exists:students,id',
        ]);

        $member = new RoomMember;
        $member->institute_id = get_institute_id();
        $member->branch_id = get_branch_id();
        $member->room_id = $request->room_id;
        $member->student_id = $request->student_id;
        $member->save();

        return $this->responseSuccess($member, 'Room members fetch successfully.');
    }

    public function show($id): JsonResponse
    {
        $student = Student::join('users', 'users.id', '=', 'students.user_id')
            ->join('student_sessions', 'students.id', '=', 'student_sessions.student_id')
            ->join('classes', 'classes.id', '=', 'student_sessions.class_id')
            ->join('sections', 'sections.id', '=', 'student_sessions.section_id')
            ->where('student_sessions.session_id', get_option('academic_year'))
            ->where('students.id', $id)->first();

        return $this->responseSuccess($student, 'Room members fetch successfully.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'student_id' => 'nullable|exists:students,id',
        ]);

        $member = RoomMember::find($id);
        if (! $member) {
            return $this->responseError([], _lang('Something went wrong. Member can not be found.'));
        }

        if ($request->student_id) {
            $member->student_id = $request->student_id;
        }
        $member->room_id = $request->room_id;
        $member->save();

        return $this->responseSuccess($member, 'Room members fetch successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $member = RoomMember::find($id);
        if (! $member) {
            return $this->responseError([], _lang('Something went wrong. Member can not be found.'));
        }

        $member->delete();

        return $this->responseSuccess([], 'Room members fetch successfully.');
    }
}
