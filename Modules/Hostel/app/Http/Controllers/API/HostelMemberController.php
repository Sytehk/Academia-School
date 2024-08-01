<?php

namespace Modules\Hostel\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Academic\Models\Student;
use Modules\Hostel\Models\HostelCategory;
use Modules\Hostel\Models\HostelMember;

class HostelMemberController extends Controller
{
    public function __construct(
        private readonly AcademicContextService $academicContext,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $instituteId = get_institute_id();
        $branchId = get_branch_id();
        $sessionId = get_option('academic_year');

        $query = Student::query()
            ->join('users', 'users.id', '=', 'students.user_id')
            ->join('student_sessions', function ($join) use ($sessionId) {
                $join->on('students.id', '=', 'student_sessions.student_id');
                if ($sessionId) {
                    $join->where('student_sessions.session_id', $sessionId);
                }
            })
            ->join('classes', 'classes.id', '=', 'student_sessions.class_id')
            ->join('sections', 'sections.id', '=', 'student_sessions.section_id')
            ->join('hostel_members', function ($join) use ($instituteId, $branchId) {
                $join->on('hostel_members.student_id', '=', 'students.id')
                    ->where('hostel_members.institute_id', $instituteId)
                    ->where('hostel_members.branch_id', $branchId);
            })
            ->join('hostel_categories', 'hostel_categories.id', '=', 'hostel_members.hostel_category_id')
            ->leftJoin('hostels', 'hostels.id', '=', 'hostel_categories.hostel_id')
            ->where('students.institute_id', $instituteId)
            ->where('students.branch_id', $branchId)
            ->where('users.user_type', 'Student')
            ->select([
                'students.id',
                'users.name',
                'users.email',
                'users.phone',
                'users.image',
                'student_sessions.roll',
                'classes.class_name',
                'sections.section_name',
                'hostel_members.id as member_id',
                'hostels.id as hostel_id',
                'hostels.hostel_name',
                'hostels.type as hostel_type',
                'hostels.address as hostel_address',
                'hostel_categories.id as category_id',
                'hostel_categories.standard',
                'hostel_categories.hostel_fee',
            ])
            ->orderByDesc('students.id');

        $students = $query->paginate(20);

        return $this->responseSuccess($students, 'Hostel members fetched successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $context = app(AcademicContextService::class);

        $request->validate([
            'student_id' => ['required', $context->tenantExists('students', 'id')],
            'hostel_category_id' => ['required', $context->tenantExists('hostel_categories', 'id')],
        ]);

        if (! $context->findOwnedStudent((int) $request->student_id)) {
            return $this->responseError([], 'Invalid student for your institute/branch.', 422);
        }

        $member = new HostelMember;
        $member->institute_id = get_institute_id();
        $member->branch_id = get_branch_id();
        $member->student_id = $request->student_id;
        $member->hostel_category_id = $request->hostel_category_id;
        $member->save();

        return $this->responseSuccess($member, 'Hostel member fetch successfully.');
    }

    public function show($id): JsonResponse
    {
        $member = HostelMember::findOrFail((int) $id);
        $sessionId = get_option('academic_year');

        $student = Student::join('users', 'users.id', '=', 'students.user_id')
            ->join('student_sessions', function ($join) use ($sessionId) {
                $join->on('students.id', '=', 'student_sessions.student_id');
                if ($sessionId) {
                    $join->where('student_sessions.session_id', $sessionId);
                }
            })
            ->join('classes', 'classes.id', '=', 'student_sessions.class_id')
            ->join('sections', 'sections.id', '=', 'student_sessions.section_id')
            ->where('students.id', $member->student_id)
            ->where('students.institute_id', get_institute_id())
            ->where('students.branch_id', get_branch_id())
            ->select('students.*', 'users.name', 'users.email', 'users.phone', 'student_sessions.roll', 'classes.class_name', 'sections.section_name')
            ->first();

        if (! $student) {
            return $this->responseError([], 'Student not found.', 404);
        }

        return $this->responseSuccess($student, 'Hostel member fetched successfully.');
    }

    public function update(Request $request, $id): JsonResponse
    {
        $member = HostelMember::findOrFail((int) $id);
        $context = app(AcademicContextService::class);

        $request->validate([
            'hostel_category_id' => ['required', $context->tenantExists('hostel_categories', 'id')],
        ]);

        $member->hostel_category_id = $request->hostel_category_id;
        $member->save();

        return $this->responseSuccess($member, 'Hostel member updated successfully.');
    }

    public function destroy($id): JsonResponse
    {
        HostelMember::findOrFail((int) $id)->delete();

        return $this->responseSuccess([], 'Hostel member deleted successfully.');
    }

    public function getCategories($hostelId): JsonResponse
    {
        // Grouped in a closure so this OR cannot escape the BelongsToTenant
        // institute/branch scope applied to HostelCategory's global scope.
        $categories = HostelCategory::where(function ($q) use ($hostelId) {
            $q->where('hostel_id', (int) $hostelId)->orWhereNull('hostel_id');
        })->get();

        return $this->responseSuccess($categories, 'Categories fetched successfully.');
    }
}
