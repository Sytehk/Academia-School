<?php

namespace Modules\Hostel\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Academic\Models\Student;
use Modules\Hostel\Models\HostelCategory;
use Modules\Hostel\Models\HostelMember;

class HostelMemberController extends Controller
{
    public function index(Request $request)
    {

        $query = Student::query()

            ->join('users', 'users.id', '=', 'students.user_id')

            ->join('student_sessions', function ($join) {

                $join->on(
                    'students.id',
                    '=',
                    'student_sessions.student_id'
                )
                    ->where(
                        'student_sessions.session_id',
                        get_option('academic_year')
                    );
            })

            ->join(
                'classes',
                'classes.id',
                '=',
                'student_sessions.class_id'
            )

            ->join(
                'sections',
                'sections.id',
                '=',
                'student_sessions.section_id'
            )

            ->join(
                'hostel_members',
                'hostel_members.student_id',
                '=',
                'students.id'
            )

            ->join(
                'hostel_categories',
                'hostel_categories.id',
                '=',
                'hostel_members.hostel_category_id'
            )

            ->leftJoin(
                'hostels',
                'hostels.id',
                '=',
                'hostel_categories.hostel_id'
            )

            ->where(
                'users.user_type',
                'Student'
            )

            ->where('students.institute_id', get_institute_id())
            ->where('students.branch_id', get_branch_id())

            ->select([

                'students.id',
                'users.name',
                'users.phone',

                'classes.class_name',
                'sections.section_name',

                'hostel_members.id as member_id',

                'hostels.hostel_name',

                'hostel_categories.standard',
                'hostel_categories.hostel_fee',

            ]);

        if ($search = $request->get('search')) {

            $query->where(function ($q) use ($search) {

                $q->where('users.name', 'like', "%$search%")
                    ->orWhere('users.phone', 'like', "%$search%")
                    ->orWhere('hostels.hostel_name', 'like', "%$search%");
            });
        }

        $members = $query
            ->orderByDesc('students.id')
            ->paginate(20);

        return view('hostel::member.index', compact('members'));
    }

    public function create()
    {

        $students = Student::query()

            ->join(
                'users',
                'users.id',
                '=',
                'students.user_id'
            )

            ->select(
                'students.id',
                'users.name'
            )

            ->where(
                'users.user_type',
                'Student'
            )

            ->where('students.institute_id', get_institute_id())
            ->where('students.branch_id', get_branch_id())

            ->get();

        $categories = HostelCategory::with('hostel')
            ->get();

        return view(
            'hostel::member.create',
            compact(
                'students',
                'categories'
            )
        );
    }

    public function store(Request $request)
    {

        $request->validate([

            'student_id' => 'required|exists:students,id',

            'hostel_category_id' => 'required|exists:hostel_categories,id',

        ]);

        HostelMember::create([

            'institute_id' => get_institute_id(),

            'branch_id' => get_branch_id(),

            'student_id' => $request->student_id,

            'hostel_category_id' => $request->hostel_category_id,

        ]);

        return redirect()
            ->route('hostel-members.index')
            ->with(
                'success',
                'Hostel member created successfully.'
            );
    }

    public function edit($id)
    {

        $member =
            HostelMember::findOrFail($id);

        $students = Student::join(
            'users',
            'users.id',
            '=',
            'students.user_id'
        )
            ->select(
                'students.id',
                'users.name'
            )
            ->where('students.institute_id', get_institute_id())
            ->where('students.branch_id', get_branch_id())
            ->get();

        $categories =
            HostelCategory::with('hostel')->get();

        return view(
            'hostel::member.edit',
            compact(
                'member',
                'students',
                'categories'
            )
        );
    }

    public function update(Request $request, $id)
    {

        $request->validate([

            'student_id' => 'required|exists:students,id',

            'hostel_category_id' => 'required|exists:hostel_categories,id',

        ]);

        $member =
            HostelMember::findOrFail($id);

        $member->update([

            'student_id' => $request->student_id,

            'hostel_category_id' => $request->hostel_category_id,

        ]);

        return redirect()
            ->route('hostel-members.index')
            ->with(
                'success',
                'Hostel member updated successfully.'
            );
    }

    public function destroy($id)
    {

        HostelMember::findOrFail($id)
            ->delete();

        return redirect()
            ->route('hostel-members.index')
            ->with(
                'success',
                'Hostel member deleted successfully.'
            );
    }

    public function get_hostel_fee(Request $request)
    {

        $category =
            HostelCategory::find(
                $request->hostel_category_id
            );

        return response()->json([

            'fee' => $category->hostel_fee ?? 0,

        ]);
    }
}
