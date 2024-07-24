<?php

namespace Modules\Examination\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Examination\Models\ExamTermDetail;
use Modules\Examination\Models\ResultPublication;
use Modules\Examination\Models\ResultPublicationLog;
use Modules\Examination\Models\StudentResultComment;
use Modules\Examination\Services\ReportCardDataService;
use Modules\Examination\Services\ResultCalculationService;
use Modules\Examination\Services\ResultPublicationService;

/**
 * Result processing workflow API: approval / publication / locking per exam +
 * class, term details for report cards, class-teacher & principal comments and
 * class statistics. Teachers (portal) may write class-teacher comments for
 * their own classes only.
 */
class ResultWorkflowController extends Controller
{
    public function __construct(
        private readonly ResultPublicationService $publications,
        private readonly ResultCalculationService $calculation,
        private readonly ReportCardDataService $reportData,
        private readonly AcademicContextService $academicContext,
    ) {}

    // ── Publication workflow ────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'exam_id' => ['required', 'integer', $this->academicContext->tenantExists('exams')],
            'class_id' => 'nullable|integer',
        ]);

        $rows = ResultPublication::with(['class:id,class_name'])
            ->where('exam_id', (int) $request->exam_id)
            ->when($request->filled('class_id'), fn ($q) => $q->where('class_id', (int) $request->class_id))
            ->get();

        return $this->responseSuccess($rows, 'Result publication status fetched.');
    }

    public function transition(Request $request, string $action): JsonResponse
    {
        $data = $request->validate([
            'exam_id' => ['required', 'integer', $this->academicContext->tenantExists('exams')],
            'class_id' => 'required|integer',
            'note' => 'nullable|string|max:1000',
        ]);
        $this->academicContext->assertClassSectionGroup((int) $data['class_id']);

        $allowed = ResultPublicationService::ACTION_PERMISSIONS[$action] ?? null;
        abort_if($allowed === null, 404);
        if (! collect($allowed)->contains(fn ($permission) => can_access_system($permission))) {
            return $this->responseError([], _lang('You are not authorized to perform this result action.'), 403);
        }

        $publication = $this->publications->perform($action, (int) $data['exam_id'], (int) $data['class_id'], $data['note'] ?? null, auth()->id());

        return $this->responseSuccess($publication, 'Result '.$action.' done.');
    }

    public function logs(Request $request): JsonResponse
    {
        $request->validate([
            'exam_id' => ['required', 'integer', $this->academicContext->tenantExists('exams')],
            'class_id' => 'required|integer',
        ]);

        $publication = ResultPublication::where('exam_id', (int) $request->exam_id)->where('class_id', (int) $request->class_id)->first();

        return $this->responseSuccess(
            $publication ? ResultPublicationLog::with('user:id,name')->where('result_publication_id', $publication->id)->latest('id')->get() : [],
            'Result workflow history fetched.'
        );
    }

    // ── Term details ────────────────────────────────────────────────────────

    public function showTerm(int $examId): JsonResponse
    {
        $this->assertOwnedExam($examId);

        return $this->responseSuccess(ExamTermDetail::where('exam_id', $examId)->first(), 'Term details fetched.');
    }

    public function updateTerm(Request $request, int $examId): JsonResponse
    {
        $this->assertOwnedExam($examId);
        $data = $request->validate([
            'term_number' => 'nullable|integer|min:1|max:4',
            'term_start_date' => 'nullable|date',
            'term_end_date' => 'nullable|date|after_or_equal:term_start_date',
            'days_school_opened' => 'nullable|integer|min:0|max:366',
            'next_term_begins' => 'nullable|date',
            'next_term_fee' => 'nullable|numeric|min:0',
            'next_term_note' => 'nullable|string|max:255',
        ]);

        $detail = ExamTermDetail::updateOrCreate(
            ['exam_id' => $examId],
            $data + [
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'session_id' => $this->academicContext->currentSessionId(),
            ]
        );

        return $this->responseSuccess($detail, 'Term details saved.');
    }

    // ── Comments ────────────────────────────────────────────────────────────

    /**
     * The class roster with each student's average, grade, existing comments
     * and the automatic remark suggested by their grade.
     */
    public function comments(Request $request): JsonResponse
    {
        [$examId, $classId, $sectionId] = $this->commentContext($request);

        $rows = $this->calculation->buildClassTermResults($classId, $examId, $sectionId);
        $existing = StudentResultComment::where('exam_id', $examId)->whereIn('student_id', $rows->pluck('student_id'))->get()->keyBy('student_id');

        return $this->responseSuccess($rows->map(function (array $row) use ($existing, $examId) {
            $comment = $existing->get($row['student_id']);
            $auto = $this->reportData->comments((int) $row['student_id'], $examId, $row['grade'] ?? null);

            return [
                'student_id' => $row['student_id'],
                'student_name' => $row['student_name'],
                'roll' => $row['roll'],
                'average' => $row['percentage'],
                'grade' => $row['grade'],
                'position' => $row['position'] ?? null,
                'teacher_comment' => $comment?->teacher_comment,
                'principal_comment' => $comment?->principal_comment,
                'suggested_comment' => $auto['teacher_comment_is_auto'] ? $auto['teacher_comment'] : null,
            ];
        })->values(), 'Result comments fetched.');
    }

    public function storeComments(Request $request): JsonResponse
    {
        [$examId, $classId, $sectionId] = $this->commentContext($request);
        $data = $request->validate([
            'comments' => 'required|array|min:1',
            'comments.*.student_id' => 'required|integer',
            'comments.*.teacher_comment' => 'nullable|string|max:1000',
            'comments.*.principal_comment' => 'nullable|string|max:1000',
        ]);

        $writesPrincipal = collect($data['comments'])->contains(fn ($c) => array_key_exists('principal_comment', $c));
        $writesTeacher = collect($data['comments'])->contains(fn ($c) => array_key_exists('teacher_comment', $c));
        $isTeacher = auth()->user()?->user_type === 'Teacher';

        if ($writesPrincipal && ($isTeacher || ! can_access_system('exam_module_principal_comment'))) {
            return $this->responseError([], _lang('Only the principal / head teacher may write principal comments.'), 403);
        }
        if ($writesTeacher && ! $isTeacher && ! can_access_system('exam_module_result_comments')) {
            return $this->responseError([], _lang('You are not authorized to write class-teacher comments.'), 403);
        }

        $this->publications->assertUnlocked($examId, $classId);
        $this->academicContext->assertStudentsInClassSection(array_column($data['comments'], 'student_id'), $classId, $sectionId);

        DB::transaction(function () use ($data, $examId, $classId) {
            foreach ($data['comments'] as $entry) {
                $values = ['class_id' => $classId, 'branch_id' => get_branch_id(), 'session_id' => $this->academicContext->currentSessionId()];
                if (array_key_exists('teacher_comment', $entry)) {
                    $values += ['teacher_comment' => $entry['teacher_comment'], 'teacher_commented_by' => auth()->id()];
                }
                if (array_key_exists('principal_comment', $entry)) {
                    $values += ['principal_comment' => $entry['principal_comment'], 'principal_commented_by' => auth()->id()];
                }

                StudentResultComment::updateOrCreate(
                    ['institute_id' => get_institute_id(), 'exam_id' => $examId, 'student_id' => (int) $entry['student_id']],
                    $values
                );
            }
        });

        return $this->responseSuccess(['saved' => count($data['comments'])], 'Result comments saved.');
    }

    // ── Statistics ──────────────────────────────────────────────────────────

    public function statistics(Request $request): JsonResponse
    {
        $request->validate([
            'exam_id' => ['required', 'integer', $this->academicContext->tenantExists('exams')],
            'class_id' => 'required|integer',
            'section_id' => 'nullable|integer',
        ]);
        $sectionId = $request->filled('section_id') ? (int) $request->section_id : null;
        $this->academicContext->assertClassSectionGroup((int) $request->class_id, $sectionId);

        $rows = $this->calculation->buildClassTermResults((int) $request->class_id, (int) $request->exam_id, $sectionId);
        $stats = $this->reportData->classStatistics($rows);

        $subjects = $this->calculation->subjectsForClassGroup((int) $request->class_id, null)
            ->map(fn ($subject) => ['subject_id' => $subject->id, 'subject_name' => $subject->subject_name] + ($stats['subjects'][$subject->id] ?? [
                'highest' => null, 'lowest' => null, 'average' => null, 'sat' => 0,
            ]))->values();

        return $this->responseSuccess([
            'class' => $stats['class'] + [
                'passed' => $rows->where('status', 'pass')->count(),
                'failed' => $rows->where('status', 'fail')->count(),
            ],
            'subjects' => $subjects,
        ], 'Class result statistics fetched.');
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /**
     * @return array{0: int, 1: int, 2: ?int}
     */
    private function commentContext(Request $request): array
    {
        $request->validate([
            'exam_id' => ['required', 'integer', $this->academicContext->tenantExists('exams')],
            'class_id' => 'required|integer',
            'section_id' => 'nullable|integer',
        ]);
        $classId = (int) $request->class_id;
        $sectionId = $request->filled('section_id') ? (int) $request->section_id : null;
        $this->academicContext->assertClassSectionGroup($classId, $sectionId);

        if (auth()->user()?->user_type === 'Teacher' && ! $this->teacherHasClass($classId, $sectionId)) {
            abort(response()->json(['status' => false, 'message' => _lang('You are not the class teacher of this class.')], 403));
        }

        return [(int) $request->exam_id, $classId, $sectionId];
    }

    /** Class teacher = assigned to the class (class_assigns) or set as the section's class teacher. */
    private function teacherHasClass(int $classId, ?int $sectionId): bool
    {
        $teacherId = DB::table('teachers')->where('user_id', auth()->id())->value('id');
        if (! $teacherId) {
            return false;
        }

        if (DB::table('class_assigns')->where('teacher_id', $teacherId)->where('class_id', $classId)->exists()) {
            return true;
        }

        return DB::table('sections')
            ->where('class_id', $classId)
            ->when($sectionId, fn ($q) => $q->where('id', $sectionId))
            ->where('class_teacher_id', $teacherId)
            ->exists();
    }

    private function assertOwnedExam(int $examId): void
    {
        abort_unless(DB::table('exams')->where('id', $examId)->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->exists(), 404);
    }
}
