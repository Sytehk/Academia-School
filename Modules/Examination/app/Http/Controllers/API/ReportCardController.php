<?php

namespace Modules\Examination\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Modules\Academic\Models\StudentSession;
use Modules\Examination\Services\ResultCalculationService;
use Modules\Examination\Services\ResultCardService;

/**
 * Report card PDFs for staff: one student, or every student of a class /
 * section (one card per page, class results computed once).
 */
class ReportCardController extends Controller
{
    /** Keeps a single PDF render within memory / time limits. */
    private const MAX_CARDS_PER_PDF = 80;

    public function __construct(
        private readonly ResultCardService $cards,
        private readonly ResultCalculationService $calculation,
        private readonly AcademicContextService $academicContext,
    ) {}

    public function studentPdf(Request $request): Response
    {
        $request->validate([
            'student_session_id' => 'required|integer',
            'exam_id' => ['required', 'integer', $this->academicContext->tenantExists('exams')],
        ]);

        $session = $this->ownedStudentSession((int) $request->student_session_id);
        $session->load(['student.user', 'session', 'class', 'section', 'group']);
        $card = $this->cards->singleTermCard($session, (int) $request->exam_id);

        return Pdf::loadView('examination::results.single_card_pdf', $card)
            ->setPaper('a4', 'portrait')
            ->download('report-card-'.$session->student_id.'-exam'.$request->exam_id.'.pdf');
    }

    public function classPdf(Request $request): Response|JsonResponse
    {
        $request->validate([
            'exam_id' => ['required', 'integer', $this->academicContext->tenantExists('exams')],
            'class_id' => 'required|integer',
            'section_id' => 'nullable|integer',
            'group_id' => 'nullable|integer',
        ]);
        $classId = (int) $request->class_id;
        $examId = (int) $request->exam_id;
        $sectionId = $request->filled('section_id') ? (int) $request->section_id : null;
        $groupId = $request->filled('group_id') ? (int) $request->group_id : null;
        $this->academicContext->assertClassSectionGroup($classId, $sectionId, $groupId);

        $rows = $this->calculation->buildClassTermResults($classId, $examId, $sectionId, $groupId);
        if ($rows->isEmpty()) {
            return $this->responseError([], _lang('No students found for this class.'), 422);
        }
        if ($rows->count() > self::MAX_CARDS_PER_PDF) {
            return $this->responseError([], _lang('Too many students for one PDF; filter by section.'), 422);
        }

        $cards = $rows->map(fn (array $row) => $this->cards->singleTermCard($row['student_session'], $examId, null, $rows))->all();

        return Pdf::loadView('examination::results.class_cards_pdf', ['cards' => $cards, 'header' => $cards[0]['header']])
            ->setPaper('a4', 'portrait')
            ->download("report-cards-class{$classId}-exam{$examId}.pdf");
    }

    /**
     * Any student session of this institute/branch — including previous academic
     * sessions, so historical result cards stay printable.
     */
    private function ownedStudentSession(int $studentSessionId): StudentSession
    {
        $session = $this->academicContext->findOwnedStudentSession($studentSessionId);
        if (! $session) {
            throw ValidationException::withMessages([
                'student_session_id' => [_lang('The selected student session is invalid for your context.')],
            ]);
        }

        return $session;
    }
}
