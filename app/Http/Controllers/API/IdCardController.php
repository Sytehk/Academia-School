<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Academic\Models\Department;
use Modules\Academic\Models\Signature;
use Modules\Academic\Models\Staff;
use Modules\Academic\Models\Teacher;
use Modules\Academic\Services\StudentService;
use Modules\SystemConfiguration\Models\Setting;

/**
 * Server-rendered (dompdf) ID card PDFs, reusing the same Blade views and
 * QR-code component the browser print pages already use, so the printed and
 * downloaded cards are identical.
 */
class IdCardController extends Controller
{
    /** Keeps a single PDF render within memory / time limits. */
    private const MAX_CARDS = 300;

    public function __construct(private readonly AcademicContextService $academicContext) {}

    public function studentsPdf(Request $request, StudentService $students): Response
    {
        $request->validate(['class_id' => 'required|integer', 'section_id' => 'nullable|integer', 'group_id' => 'nullable|integer', 'validity' => 'nullable|date']);
        $classId = (int) $request->class_id;
        $sectionId = $request->filled('section_id') ? (int) $request->section_id : null;
        $groupId = $request->filled('group_id') ? (int) $request->group_id : null;
        $this->academicContext->assertClassSectionGroup($classId, $sectionId, $groupId);

        $list = $students->getStudentsByClassSectionGroup($classId, $sectionId ?? 0, $groupId ?? 0, 0, get_institute_id(), get_branch_id());
        if (count($list) > self::MAX_CARDS) {
            $this->tooMany();
        }

        return Pdf::loadView('backend.students.id-card.card-list', [
            'students' => $list,
            'validity_date' => $request->input('validity'),
            'principalSignature' => $this->signature(),
            'instituteId' => get_institute_id(),
            'branchId' => get_branch_id(),
            'settings' => $this->settings(),
        ])->setPaper('a4', 'portrait')->download('student-id-cards.pdf');
    }

    public function teachersPdf(Request $request): Response
    {
        $request->validate(['department_id' => 'nullable|integer']);

        $query = Teacher::select('teachers.*', 'teachers.id as id', 'teachers.status as teacher_status', 'departments.department_name', 'users.image as user_image', 'users.name as user_name')
            ->where('teachers.institute_id', get_institute_id())
            ->where('teachers.branch_id', get_branch_id())
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->leftJoin('departments', 'departments.id', '=', 'teachers.department_id')
            ->orderBy('teachers.id', 'desc');

        if ($request->filled('department_id')) {
            $query->where('teachers.department_id', (int) $request->department_id);
        }

        $teachers = $query->get();
        if ($teachers->count() > self::MAX_CARDS) {
            $this->tooMany();
        }

        return Pdf::loadView('backend.teachers.id-card.card-list', [
            'teachers' => $teachers,
            'principalSignature' => $this->signature(),
            'instituteId' => get_institute_id(),
            'branchId' => get_branch_id(),
            'settings' => $this->settings(),
        ])->setPaper('a4', 'portrait')->download('teacher-id-cards.pdf');
    }

    public function staffPdf(Request $request): Response
    {
        $request->validate(['department_id' => 'nullable|integer']);

        $query = Staff::select('staffs.*', 'staffs.id as id', 'departments.department_name', 'users.image as user_image', 'users.name as user_name')
            ->where('staffs.institute_id', get_institute_id())
            ->where('staffs.branch_id', get_branch_id())
            ->join('users', 'users.id', '=', 'staffs.user_id')
            ->leftJoin('departments', 'departments.id', '=', 'staffs.department_id');

        if ($request->filled('department_id')) {
            $query->where('staffs.department_id', (int) $request->department_id);
        }

        $staffs = $query->get();
        if ($staffs->count() > self::MAX_CARDS) {
            $this->tooMany();
        }

        return Pdf::loadView('backend.staffs.id-card.card-list', [
            'staffs' => $staffs,
            'principalSignature' => $this->signature(),
            'instituteId' => get_institute_id(),
            'branchId' => get_branch_id(),
            'settings' => $this->settings(),
            'validityDate' => null,
        ])->setPaper('a4', 'portrait')->download('staff-id-cards.pdf');
    }

    public function departments(): JsonResponse
    {
        return $this->responseSuccess(
            Department::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->orderBy('department_name')->get(['id', 'department_name']),
            'Departments fetched.'
        );
    }

    private function signature(): ?Signature
    {
        return Signature::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->first();
    }

    private function settings(): array
    {
        return Setting::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->pluck('value', 'name')->toArray();
    }

    private function tooMany(): never
    {
        throw new HttpResponseException(
            response()->json(['status' => false, 'message' => _lang('Too many people selected for one PDF; filter to a smaller group.')], 422)
        );
    }
}
