<?php

namespace Modules\Examination\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\StudentSession;
use Modules\Examination\Models\ClassExam;
use Modules\Examination\Models\ResultPublication;
use Modules\Examination\Services\ResultCardService;

/**
 * Term results for the student portal (own results) and the parent portal
 * (the selected child). Only PUBLISHED results are ever returned — marks being
 * entered, awaiting approval, or unpublished stay invisible to families.
 */
class PortalResultController extends Controller
{
    public function __construct(
        private readonly ResultCardService $cards,
        private readonly AcademicContextService $academicContext,
    ) {}

    public function index(): JsonResponse
    {
        $session = $this->session();
        if (! $session) {
            return $this->responseError([], _lang('Student session not found.'), 404);
        }

        $published = ResultPublication::where('class_id', $session->class_id)
            ->where('status', ResultPublication::STATUS_PUBLISHED)
            ->pluck('published_at', 'exam_id');

        $exams = ClassExam::with('exam:id,name')
            ->where('class_id', $session->class_id)
            ->get()
            ->map(fn (ClassExam $classExam) => [
                'exam_id' => $classExam->exam_id,
                'exam_name' => $classExam->exam?->name,
                'published' => $published->has($classExam->exam_id),
                'published_at' => $published->get($classExam->exam_id),
            ])
            ->values();

        return $this->responseSuccess($exams, 'Results fetched.');
    }

    public function show(int $examId): JsonResponse
    {
        $session = $this->session();
        if (! $session) {
            return $this->responseError([], _lang('Student session not found.'), 404);
        }
        if (! $this->isPublished($session, $examId)) {
            return $this->responseError([], _lang('This result has not been published yet.'), 403);
        }

        return $this->responseSuccess($this->card($session, $examId), 'Result card fetched.');
    }

    public function pdf(int $examId): Response|JsonResponse
    {
        $session = $this->session();
        if (! $session) {
            return $this->responseError([], _lang('Student session not found.'), 404);
        }
        if (! $this->isPublished($session, $examId)) {
            return $this->responseError([], _lang('This result has not been published yet.'), 403);
        }

        return Pdf::loadView('examination::results.single_card_pdf', $this->card($session, $examId))
            ->setPaper('a4', 'portrait')
            ->download('report-card-exam'.$examId.'.pdf');
    }

    private function card(StudentSession $session, int $examId): array
    {
        $session->load(['student.user', 'session', 'class', 'section', 'group']);

        return $this->cards->singleTermCard($session, $examId);
    }

    private function isPublished(StudentSession $session, int $examId): bool
    {
        return ResultPublication::where('exam_id', $examId)
            ->where('class_id', $session->class_id)
            ->where('status', ResultPublication::STATUS_PUBLISHED)
            ->exists();
    }

    /** The student whose results are shown: yourself, or a parent's selected child. */
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
