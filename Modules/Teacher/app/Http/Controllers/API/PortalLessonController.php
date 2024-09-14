<?php

namespace Modules\Teacher\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\StudentSession;
use Modules\Teacher\Services\LessonPlanService;

/**
 * Lessons for the student portal (own class/section) and the parent portal
 * (selected child's class/section): published lessons only.
 */
class PortalLessonController extends Controller
{
    public function __construct(
        private readonly LessonPlanService $lessons,
        private readonly AcademicContextService $academicContext,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $session = $this->session();
        if (! $session) {
            return $this->responseSuccess([], 'No student session found.');
        }

        return $this->responseSuccess(
            $this->lessons->filter(
                $this->lessons->visibleTo($session),
                $request->only(['subject_id', 'term_number', 'week_number', 'search']),
                (int) $request->input('per_page', 20)
            ),
            'Lessons fetched successfully.'
        );
    }

    public function show(int $id): JsonResponse
    {
        $session = $this->session();
        $lesson = $session ? $this->lessons->visibleTo($session)->whereKey($id)
            ->with(['attachments', 'subject:id,subject_name'])->first() : null;
        abort_unless($lesson, 404, _lang('Lesson not found.'));

        return $this->responseSuccess($lesson, 'Lesson fetched successfully.');
    }

    private function session(): ?StudentSession
    {
        $user = auth()->user();
        $studentId = match ($user?->user_type) {
            'Student' => Student::where('user_id', $user->id)->value('id'),
            'Parent' => get_authenticated_parent_student_id(),
            default => null,
        };

        return $studentId ? $this->academicContext->studentSessionForStudent((int) $studentId) : null;
    }
}
