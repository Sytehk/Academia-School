<?php

namespace Modules\Examination\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Illuminate\Http\JsonResponse;
use Modules\Academic\Models\Student;
use Modules\Examination\Services\AcademicRecordService;

/**
 * Multi-session academic record: staff see every result; students and parents
 * see published results only.
 */
class AcademicRecordController extends Controller
{
    public function __construct(
        private readonly AcademicRecordService $records,
        private readonly AcademicContextService $academicContext,
    ) {}

    public function show(int $studentId): JsonResponse
    {
        $student = $this->academicContext->findOwnedStudent($studentId);
        abort_unless($student, 404);

        return $this->responseSuccess($this->records->forStudent($student, false), 'Academic record fetched.');
    }

    public function mine(): JsonResponse
    {
        $user = auth()->user();
        $studentId = match ($user?->user_type) {
            'Student' => Student::where('user_id', $user->id)->value('id'),
            'Parent' => get_authenticated_parent_student_id(),
            default => null,
        };
        $student = $studentId ? Student::find($studentId) : null;
        if (! $student) {
            return $this->responseError([], _lang('Student not found.'), 404);
        }

        return $this->responseSuccess($this->records->forStudent($student, true), 'Academic record fetched.');
    }
}
