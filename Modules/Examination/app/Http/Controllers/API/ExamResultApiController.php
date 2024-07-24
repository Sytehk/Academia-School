<?php

namespace Modules\Examination\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Academic\Models\StudentSession;
use Modules\Examination\Models\AssessmentDomain;
use Modules\Examination\Models\AssessmentItem;
use Modules\Examination\Models\ResultCardSetting;
use Modules\Examination\Services\EmptyMarkSheetService;
use Modules\Examination\Services\ResultCardService;
use Modules\Examination\Services\StudentAssessmentService;
use Modules\Examination\Services\TabulationSheetService;

class ExamResultApiController extends Controller
{
    public function __construct(
        private readonly TabulationSheetService $tabulationSheetService,
        private readonly ResultCardService $resultCardService,
        private readonly EmptyMarkSheetService $emptyMarkSheetService,
        private readonly AcademicContextService $academicContext,
    ) {}

    public function results(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'exam_id' => 'required|exists:exams,id',
            'section_id' => 'nullable|exists:sections,id',
            'group_id' => 'nullable|exists:student_groups,id',
        ]);

        $this->validateExamContext($request);

        $sheet = $this->tabulationSheetService->build(
            (int) $request->class_id,
            (int) $request->exam_id,
            $request->filled('section_id') ? (int) $request->section_id : null,
            $request->filled('group_id') ? (int) $request->group_id : null,
        );

        $passCount = collect($sheet['rows'])->where('status', 'pass')->count();
        $failCount = collect($sheet['rows'])->where('status', 'fail')->count();

        return $this->responseSuccess([
            'summary' => [
                'total_students' => $sheet['student_count'],
                'passed' => $passCount,
                'failed' => $failCount,
            ],
            'subjects' => $sheet['subjects'],
            'data' => $sheet['rows'],
        ], 'Exam results fetched successfully.');
    }

    public function resultCard(Request $request): JsonResponse
    {
        $request->validate([
            'student_session_id' => 'required|exists:student_sessions,id',
            'exam_id' => 'required|exists:exams,id',
        ]);
        $session = $this->ownedStudentSession((int) $request->student_session_id);
        $session->load(['student.user', 'session', 'class', 'section', 'group']);

        return $this->responseSuccess(
            $this->resultCardService->singleTermCard($session, (int) $request->exam_id),
            'Result card fetched successfully.'
        );
    }

    public function annualResultCard(Request $request): JsonResponse
    {
        $request->validate([
            'student_session_id' => 'required|exists:student_sessions,id',
        ]);
        $session = $this->ownedStudentSession((int) $request->student_session_id);
        $session->load(['student.user', 'session', 'class', 'section', 'group']);

        return $this->responseSuccess(
            $this->resultCardService->annualCard($session),
            'Annual result card fetched successfully.'
        );
    }

    public function tabulation(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'exam_id' => 'required|exists:exams,id',
            'section_id' => 'nullable|exists:sections,id',
            'group_id' => 'nullable|exists:student_groups,id',
        ]);

        $this->validateExamContext($request);

        return $this->responseSuccess(
            $this->tabulationSheetService->build(
                (int) $request->class_id,
                (int) $request->exam_id,
                $request->filled('section_id') ? (int) $request->section_id : null,
                $request->filled('group_id') ? (int) $request->group_id : null,
                ! $request->boolean('with_results'),
            ),
            'Tabulation sheet generated successfully.'
        );
    }

    public function emptyMarkSheet(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'exam_id' => 'required|exists:exams,id',
            'section_id' => 'nullable|exists:sections,id',
            'group_id' => 'nullable|exists:student_groups,id',
            'subject_ids' => 'nullable|array',
            'student_limit' => 'nullable|integer|min:1',
        ]);

        $this->validateExamContext($request);

        return $this->responseSuccess(
            $this->emptyMarkSheetService->build(
                (int) $request->class_id,
                (int) $request->exam_id,
                $request->filled('section_id') ? (int) $request->section_id : null,
                $request->filled('group_id') ? (int) $request->group_id : null,
                array_map('intval', (array) $request->input('subject_ids', [])),
                $request->filled('student_limit') ? (int) $request->student_limit : null,
            ),
            'Empty mark sheet generated successfully.'
        );
    }

    public function resultCardSettings(): JsonResponse
    {
        return $this->responseSuccess($this->resultCardService->settings(), 'Result card settings fetched.');
    }

    public function updateResultCardSettings(Request $request): JsonResponse
    {
        $request->validate([
            'result_title' => 'nullable|string|max:150',
            'settings' => 'nullable|array',
            'settings.template' => ['nullable', 'in:'.implode(',', ResultCardSetting::TEMPLATES)],
        ]);

        $settings = array_replace_recursive(ResultCardSetting::defaults(), $request->input('settings', []));
        $row = ResultCardSetting::updateOrCreate(
            [
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'session_id' => get_option('academic_year') ?: null,
            ],
            [
                'result_title' => $request->input('result_title'),
                'settings' => $settings,
            ]
        );

        return $this->responseSuccess(['row' => $row, 'settings' => $row->mergedSettings()], 'Result card settings saved.');
    }

    public function assessmentDomains(): JsonResponse
    {
        return $this->responseSuccess(
            AssessmentDomain::with('items')->orderBy('display_order')->get(),
            'Assessment domains fetched.'
        );
    }

    public function storeAssessmentDomain(Request $request): JsonResponse
    {
        $data = $request->validate([
            'domain_type' => 'required|in:cognitive,affective,psychomotor',
            'name' => 'required|string|max:120',
            'display_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);
        $domain = AssessmentDomain::create($data + [
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'session_id' => get_option('academic_year') ?: null,
        ]);

        return $this->responseSuccess($domain, 'Assessment domain saved.');
    }

    public function storeAssessmentItem(Request $request): JsonResponse
    {
        $data = $request->validate([
            'assessment_domain_id' => 'required|integer|tenant_exists:assessment_domains,id',
            'name' => 'required|string|max:150',
            'max_score' => 'required|numeric|min:0',
            'rating_type' => 'required|in:numeric,scale,grade',
            'grade_scale' => 'nullable|array',
            'display_order' => 'nullable|integer',
            'remarks_enabled' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);
        $item = AssessmentItem::create($data + [
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
        ]);

        return $this->responseSuccess($item, 'Assessment item saved.');
    }

    public function storeStudentAssessments(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|integer',
            'section_id' => 'nullable|integer',
            'exam_id' => ['required', 'integer', $this->academicContext->tenantExists('exams')],
            'entries' => 'required|array',
            'entries.*' => 'array',
            'entries.*.*.score' => 'nullable|numeric|min:0',
            'entries.*.*.rating' => 'nullable|string|max:80',
            'entries.*.*.remarks' => 'nullable|string|max:255',
        ]);

        $saved = app(StudentAssessmentService::class)->save(
            (int) $request->class_id,
            $request->filled('section_id') ? (int) $request->section_id : null,
            (int) $request->exam_id,
            $request->entries,
        );

        return $this->responseSuccess(['saved' => $saved], 'Domain assessments saved.');
    }

    private function validateExamContext(Request $request): void
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
