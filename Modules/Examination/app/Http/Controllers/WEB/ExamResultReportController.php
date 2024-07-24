<?php

namespace Modules\Examination\Http\Controllers\WEB;

use App\Exports\MeritListExport;
use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Academic\Models\Exam;
use Modules\Academic\Services\ClassService;
use Modules\Academic\Services\SectionService;
use Modules\Examination\Services\EmptyMarkSheetService;
use Modules\Examination\Services\ResultCalculationService;
use Modules\Examination\Services\ResultCardService;
use Modules\Examination\Services\ResultNotificationService;
use Modules\Examination\Services\TabulationSheetService;
use Pdf;
use RealRashid\SweetAlert\Facades\Alert;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExamResultReportController extends Controller
{
    public function __construct(
        private readonly ClassService $classService,
        private readonly SectionService $sectionService,
        private readonly ResultCalculationService $resultCalculationService,
        private readonly ResultCardService $resultCardService,
        private readonly TabulationSheetService $tabulationSheetService,
        private readonly EmptyMarkSheetService $emptyMarkSheetService,
        private readonly AcademicContextService $academicContext,
        private readonly ResultNotificationService $resultNotificationService,
    ) {}

    public function examResultView(Request $request): Factory|View
    {
        $this->authorizeResult();

        $results = collect();
        $exam = null;

        if ($request->filled('class_id') && $request->filled('exam_id')) {
            $this->validateExamFilters($request);
            $results = $this->tabulationSheetService->build(
                (int) $request->class_id,
                (int) $request->exam_id,
                $request->filled('section_id') ? (int) $request->section_id : null,
                $request->filled('group_id') ? (int) $request->group_id : null,
            );
            $exam = Exam::find((int) $request->exam_id);
        }

        return view('examination::results.index', [
            'classes' => $this->classService->getClasses(),
            'sections' => $this->scopedSections($request),
            'exams' => Exam::orderBy('name')->get(['id', 'name']),
            'results' => $results,
            'exam' => $exam,
            'filters' => $request->only(['class_id', 'section_id', 'group_id', 'exam_id', 'search']),
        ]);
    }

    public function sendNotifications(Request $request): RedirectResponse
    {
        $this->authorizeResult();

        // exam_id also accepts the literal 'grand_final' sentinel — Grand Final has no row
        // in the exams table, it is a computed combination of a class's other exams
        // (FEAT-009), so it cannot be validated with exists:exams,id.
        $request->validate([
            'exam_id' => ['required', function ($attribute, $value, $fail) {
                if ($value !== 'grand_final' && ! Exam::where('id', $value)->exists()) {
                    $fail(_lang('The selected exam is invalid.'));
                }
            }],
            'notify_channel' => 'required|in:sms,whatsapp,both',
            'scope' => 'required|in:current,class,classes,institute',
            'class_id' => 'required_unless:scope,institute,classes|nullable|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'class_ids' => 'required_if:scope,classes|array',
            'class_ids.*' => 'integer|exists:classes,id',
        ]);

        $isGrandFinal = $request->exam_id === 'grand_final';

        if (! $isGrandFinal && in_array($request->scope, ['current', 'class'], true)) {
            $this->validateExamFilters($request);
        }

        $summary = $isGrandFinal
            ? $this->resultNotificationService->queueGrandFinal($request)
            : $this->resultNotificationService->queue($request);

        Alert::toast(
            _lang('Result notifications queued').': '.$summary['queued'].'. '._lang('Skipped').': '.$summary['skipped'].'.',
            'success'
        );

        return back();
    }

    public function singleCard(Request $request): Factory|View
    {
        $this->authorizeResult();
        $payload = $this->singleCardPayload($request);

        return view('examination::results.single_card', $payload);
    }

    public function singleCardPdf(Request $request): Response
    {
        $this->authorizeResult();
        $payload = $this->singleCardPayload($request);
        $pdf = Pdf::loadView('examination::results.single_card_pdf', $payload)->setPaper('a4', 'portrait');

        return $pdf->download('result-card-'.now()->format('Y-m-d').'.pdf');
    }

    public function studentMarkView(Request $request): Factory|View
    {
        $this->authorizeResult();
        $payload = $this->singleCardPayload($request);

        return view('examination::results.student_marks', $payload);
    }

    public function studentMarkPdf(Request $request): Response
    {
        $this->authorizeResult();
        $payload = $this->singleCardPayload($request);
        $pdf = Pdf::loadView('examination::results.student_marks', $payload + ['hideToolbar' => true])->setPaper('a4', 'portrait');

        return $pdf->download('student-marks-'.now()->format('Y-m-d').'.pdf');
    }

    public function annualCard(Request $request): Factory|View
    {
        $this->authorizeResult();
        $payload = $this->annualCardPayload($request);

        return view('examination::results.annual_card', $payload);
    }

    public function annualCardPdf(Request $request): Response
    {
        $this->authorizeResult();
        $payload = $this->annualCardPayload($request);
        $pdf = Pdf::loadView('examination::results.annual_card', $payload + ['hideToolbar' => true])->setPaper('a4', 'portrait');

        return $pdf->download('annual-result-'.now()->format('Y-m-d').'.pdf');
    }

    public function tabulation(Request $request): Factory|View
    {
        $this->authorizeResult();

        $sheet = null;
        if ($request->filled('class_id') && $request->filled('exam_id')) {
            $this->validateExamFilters($request);
            // with_results: 0 = blank (manual entry), 1 = calculated with position,
            // 2 = calculated without position (FEAT-006).
            $sheetType = (string) $request->input('with_results', '0');
            $blankMarks = $sheetType === '0';
            $showPosition = $sheetType !== '2';
            $sheet = $this->tabulationSheetService->build(
                (int) $request->class_id,
                (int) $request->exam_id,
                $request->filled('section_id') ? (int) $request->section_id : null,
                $request->filled('group_id') ? (int) $request->group_id : null,
                $blankMarks,
                $showPosition,
            );
            $sheet['exam_name'] = Exam::find((int) $request->exam_id)?->name;
            $sheet['header'] = $this->resultCardService->schoolHeader();
        }

        return view('examination::results.tabulation', [
            'classes' => $this->classService->getClasses(),
            'sections' => $this->scopedSections($request),
            'exams' => Exam::orderBy('name')->get(['id', 'name']),
            'sheet' => $sheet,
            'filters' => $request->only(['class_id', 'section_id', 'group_id', 'exam_id', 'with_results']),
        ]);
    }

    public function tabulationPrint(Request $request): Factory|View
    {
        $this->authorizeResult();
        $sheet = $this->requireSheet($request);
        $sheet['header'] = $this->resultCardService->schoolHeader();
        $sheet['exam_name'] = Exam::find((int) $request->exam_id)?->name;

        return view('examination::results.tabulation_print', compact('sheet'));
    }

    public function tabulationPdf(Request $request): Response
    {
        $this->authorizeResult();
        $sheet = $this->requireSheet($request);
        $sheet['header'] = $this->resultCardService->schoolHeader();
        $sheet['exam_name'] = Exam::find((int) $request->exam_id)?->name;
        $paper = ($sheet['paper'] ?? 'a4') === 'a3' ? 'a3' : 'a4';
        $pdf = Pdf::loadView('examination::results.tabulation_print', compact('sheet'))
            ->setPaper($paper, 'landscape');

        return $pdf->download('tabulation-sheet-'.now()->format('Y-m-d').'.pdf');
    }

    public function meritList(Request $request): Factory|View
    {
        $this->authorizeResult();

        $sheet = null;
        if ($request->filled('class_id') && $request->filled('exam_id')) {
            $sheet = $this->buildMeritList($request);
        }

        return view('examination::results.merit_list', [
            'classes' => $this->classService->getClasses(),
            'sections' => $this->scopedSections($request),
            'exams' => Exam::orderBy('name')->get(['id', 'name']),
            'sheet' => $sheet,
            'filters' => $request->only(['class_id', 'section_id', 'exam_id']),
        ]);
    }

    public function meritListPrint(Request $request): Factory|View
    {
        $this->authorizeResult();
        $sheet = $this->requireMeritList($request);

        return view('examination::results.merit_list_print', compact('sheet'));
    }

    public function meritListPdf(Request $request): Response
    {
        $this->authorizeResult();
        $sheet = $this->requireMeritList($request);
        $pdf = Pdf::loadView('examination::results.merit_list_print', compact('sheet'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('merit-list-'.now()->format('Y-m-d').'.pdf');
    }

    public function meritListExcel(Request $request): BinaryFileResponse
    {
        $this->authorizeResult();
        $sheet = $this->requireMeritList($request);

        return Excel::download(
            new MeritListExport(collect($sheet['rows'])),
            'merit-list-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    /**
     * Merit List reuses TabulationSheetService's mark/GPA/grade/position calculation —
     * the only difference is the rows are re-sorted by position (ascending) instead of
     * roll number, and the exported columns are a slimmer subset (FEAT-008).
     */
    private function buildMeritList(Request $request): array
    {
        $this->validateExamFilters($request);

        $sheet = $this->tabulationSheetService->build(
            (int) $request->class_id,
            (int) $request->exam_id,
            $request->filled('section_id') ? (int) $request->section_id : null,
            null,
            false,
            true,
        );

        $sheet['rows'] = collect($sheet['rows'])
            ->sortBy(fn ($row) => $row['position'] ?? PHP_INT_MAX)
            ->values();

        $sheet['exam_name'] = Exam::find((int) $request->exam_id)?->name;
        $sheet['header'] = $this->resultCardService->schoolHeader();

        return $sheet;
    }

    private function requireMeritList(Request $request): array
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'exam_id' => 'required|exists:exams,id',
        ]);

        return $this->buildMeritList($request);
    }

    public function emptyMarkSheet(Request $request): Factory|View
    {
        $this->authorizeMarkInput();

        $sheet = null;
        if ($request->filled('class_id') && $request->filled('exam_id')) {
            $this->validateExamFilters($request);
            $subjectIds = array_filter(array_map('intval', (array) $request->input('subject_ids', [])));
            $sheet = $this->emptyMarkSheetService->build(
                (int) $request->class_id,
                (int) $request->exam_id,
                $request->filled('section_id') ? (int) $request->section_id : null,
                $request->filled('group_id') ? (int) $request->group_id : null,
                $subjectIds,
                $request->filled('student_limit') ? (int) $request->student_limit : null,
            );
            $sheet['exam_name'] = Exam::find((int) $request->exam_id)?->name;
        }

        return view('examination::results.empty_mark_sheet', [
            'classes' => $this->classService->getClasses(),
            'sections' => $this->scopedSections($request),
            'exams' => Exam::orderBy('name')->get(['id', 'name']),
            'sheet' => $sheet,
            'filters' => $request->all(),
        ]);
    }

    public function emptyMarkSheetPrint(Request $request): Factory|View
    {
        $this->authorizeMarkInput();
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'exam_id' => 'required|exists:exams,id',
        ]);

        $this->validateExamFilters($request);

        $subjectIds = array_filter(array_map('intval', (array) $request->input('subject_ids', [])));
        $sheet = $this->emptyMarkSheetService->build(
            (int) $request->class_id,
            (int) $request->exam_id,
            $request->filled('section_id') ? (int) $request->section_id : null,
            $request->filled('group_id') ? (int) $request->group_id : null,
            $subjectIds,
            $request->filled('student_limit') ? (int) $request->student_limit : null,
        );
        $sheet['exam_name'] = Exam::find((int) $request->exam_id)?->name;

        return view('examination::results.empty_mark_sheet_print', compact('sheet'));
    }

    private function singleCardPayload(Request $request): array
    {
        $request->validate([
            'student_session_id' => 'required|exists:student_sessions,id',
            'exam_id' => 'required|exists:exams,id',
        ]);

        $session = $this->academicContext->assertOwnedStudentSession((int) $request->student_session_id);
        $session->load(['student.user', 'session', 'class', 'section', 'group']);

        return $this->resultCardService->singleTermCard($session, (int) $request->exam_id);
    }

    private function annualCardPayload(Request $request): array
    {
        $request->validate([
            'student_session_id' => 'required|exists:student_sessions,id',
        ]);

        $session = $this->academicContext->assertOwnedStudentSession((int) $request->student_session_id);
        $session->load(['student.user', 'session', 'class', 'section', 'group']);

        return $this->resultCardService->annualCard($session);
    }

    private function requireSheet(Request $request): array
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'exam_id' => 'required|exists:exams,id',
        ]);

        $this->validateExamFilters($request);

        $sheetType = (string) $request->input('with_results', '0');
        $blankMarks = $sheetType === '0';
        $showPosition = $sheetType !== '2';

        return $this->tabulationSheetService->build(
            (int) $request->class_id,
            (int) $request->exam_id,
            $request->filled('section_id') ? (int) $request->section_id : null,
            $request->filled('group_id') ? (int) $request->group_id : null,
            $blankMarks,
            $showPosition,
        );
    }

    private function authorizeResult(): void
    {
        abort_unless(auth()->user()?->can('exam_module_exam_result') || auth()->user()?->can('exam_module_print_result') || auth()->user()?->can('exam_module_view_broad_sheet'), 403);
    }

    private function authorizeMarkInput(): void
    {
        abort_unless(auth()->user()?->can('exam_module_mark_input'), 403);
    }

    private function scopedSections(Request $request)
    {
        if (! $request->filled('class_id')) {
            return collect();
        }

        return $this->academicContext->sectionsForClass((int) $request->class_id);
    }

    private function validateExamFilters(Request $request): void
    {
        if (! $request->filled('class_id')) {
            return;
        }

        $this->academicContext->assertClassSectionGroup(
            (int) $request->class_id,
            $request->filled('section_id') ? (int) $request->section_id : null,
            $request->filled('group_id') ? (int) $request->group_id : null,
        );
    }
}
