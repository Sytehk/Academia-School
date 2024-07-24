<?php

namespace Modules\Examination\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Academic\Models\Subject;
use Modules\Examination\Exceptions\OnlineExamException;
use Modules\Examination\Models\OnlineExam;
use Modules\Examination\Models\OnlineExamAttempt;
use Modules\Examination\Models\OnlineExamAttemptQuestion;
use Modules\Examination\Models\OnlineExamQuestion;
use Modules\Examination\Models\OnlineExamQuestionPool;
use Modules\Examination\Services\OnlineExamAccess;
use Modules\Examination\Services\OnlineExamQuestionService;
use Modules\Examination\Services\OnlineExamReportService;
use Modules\Examination\Services\OnlineExamResultTransferService;
use Modules\Examination\Services\OnlineExamScoringService;

/**
 * Online exam (CBT) management API, shared by the back-office routes
 * (`/api/online-exams…`, permission-gated) and the teacher portal
 * (`/api/teacher/online-exams…`, limited to the teacher's own subjects).
 */
class OnlineExamController extends Controller
{
    /** Settings that may still change once students have attempted the exam. */
    private const EDITABLE_AFTER_ATTEMPTS = [
        'name', 'instructions', 'end_at', 'attempt_limit', 'score_policy', 'result_visibility',
        'transfer_policy', 'mark_component_slot', 'conversion_enabled', 'online_full_marks', 'academic_full_marks',
    ];

    public function __construct(
        private readonly OnlineExamAccess $access,
        private readonly OnlineExamQuestionService $questions,
        private readonly OnlineExamScoringService $scoring,
        private readonly OnlineExamReportService $reports,
        private readonly OnlineExamResultTransferService $transfers,
        private readonly AcademicContextService $academicContext,
    ) {}

    // ── Exams ────────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $query = OnlineExam::with(['exam:id,name', 'class:id,class_name', 'section:id,section_name', 'subject:id,subject_name'])
            ->withCount('attempts')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('class_id'), fn ($q) => $q->where('class_id', (int) $request->class_id))
            ->when($request->filled('subject_id'), fn ($q) => $q->where('subject_id', (int) $request->subject_id))
            ->when($request->filled('exam_id'), fn ($q) => $q->where('exam_id', (int) $request->exam_id))
            ->latest('id');

        return $this->responseSuccess($this->access->scope($query)->get(), 'Online exams fetched.');
    }

    public function show(int $id): JsonResponse
    {
        $exam = $this->find($id);
        $exam->load(['exam:id,name', 'class:id,class_name', 'section:id,section_name', 'subject:id,subject_name', 'questions', 'pools']);

        return $this->responseSuccess($exam->toArray() + [
            'locked' => $this->questions->isLocked($exam),
            'paper' => $this->scoring->paperShape($exam),
            'publish_problems' => $this->questions->publishProblems($exam),
        ], 'Online exam fetched.');
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateExam($request);

        $exam = OnlineExam::create($data + [
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'session_id' => $this->academicContext->currentSessionId(),
            'status' => OnlineExam::STATUS_DRAFT,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        return $this->responseSuccess($exam->fresh(), 'Online exam created as a draft.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $exam = $this->find($id);
        $data = $this->validateExam($request, $exam);

        if ($this->questions->isLocked($exam)) {
            $blocked = array_diff(array_keys($data), self::EDITABLE_AFTER_ATTEMPTS);
            $changed = array_filter($blocked, fn ($key) => (string) $exam->{$key} !== (string) $data[$key]);
            if ($changed !== []) {
                return $this->responseError(
                    ['fields' => array_values($changed)],
                    _lang('Students have already attempted this exam; only its name, instructions, end time and result settings can change.'),
                    422
                );
            }
        }

        $exam->update($data);
        $this->questions->refreshTotals($exam);

        return $this->responseSuccess($exam->fresh(), 'Online exam updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $exam = $this->find($id);
        if ($this->questions->isLocked($exam)) {
            return $this->responseError([], _lang('This exam has attempts and cannot be deleted. Close it instead.'), 422);
        }

        $exam->delete();

        return $this->responseSuccess([], 'Online exam deleted.');
    }

    public function publish(int $id): JsonResponse
    {
        $exam = $this->find($id);
        if ($exam->status === OnlineExam::STATUS_PUBLISHED) {
            return $this->responseSuccess($exam, 'Online exam is already published.');
        }

        $problems = $this->questions->publishProblems($exam);
        if ($problems !== []) {
            return $this->responseError(['problems' => $problems], _lang('The exam is not ready to publish.'), 422);
        }

        $exam->update([
            'status' => OnlineExam::STATUS_PUBLISHED,
            'is_active' => true,
            'published_at' => $exam->published_at ?? now(),
        ]);

        return $this->responseSuccess($exam->fresh(), 'Online exam published.');
    }

    public function unpublish(int $id): JsonResponse
    {
        $exam = $this->find($id);
        if ($this->questions->isLocked($exam)) {
            return $this->responseError([], _lang('Students have already attempted this exam. Close it instead.'), 422);
        }

        $exam->update(['status' => OnlineExam::STATUS_DRAFT]);

        return $this->responseSuccess($exam->fresh(), 'Online exam moved back to draft.');
    }

    public function close(int $id): JsonResponse
    {
        $exam = $this->find($id);
        $submitted = $this->scoring->closeExam($exam);

        return $this->responseSuccess(
            ['exam' => $exam->fresh(), 'auto_submitted_attempts' => $submitted],
            'Online exam closed.'
        );
    }

    // ── Pools ────────────────────────────────────────────────────────────────

    public function pools(int $id): JsonResponse
    {
        $exam = $this->find($id);

        return $this->responseSuccess($exam->pools()->withCount('questions')->get(), 'Question pools fetched.');
    }

    public function storePool(Request $request, int $id): JsonResponse
    {
        $exam = $this->find($id);
        $data = $request->validate(['name' => 'required|string|max:120', 'draw_count' => 'required|integer|min:1|max:500']);

        return $this->guard(fn () => $this->responseSuccess(
            $this->questions->createPool($exam, $data['name'], (int) $data['draw_count']),
            'Question pool created.'
        ));
    }

    public function updatePool(Request $request, int $poolId): JsonResponse
    {
        $pool = $this->findPool($poolId);
        $data = $request->validate(['name' => 'sometimes|string|max:120', 'draw_count' => 'sometimes|integer|min:1|max:500']);

        return $this->guard(fn () => $this->responseSuccess($this->questions->updatePool($pool, $data), 'Question pool updated.'));
    }

    public function destroyPool(int $poolId): JsonResponse
    {
        $pool = $this->findPool($poolId);

        return $this->guard(function () use ($pool) {
            $this->questions->deletePool($pool);

            return $this->responseSuccess([], 'Question pool deleted.');
        });
    }

    // ── Questions ────────────────────────────────────────────────────────────

    public function storeQuestion(Request $request, int $id): JsonResponse
    {
        $this->authorizeAction('exam_module_manage_online_questions');
        $exam = $this->find($id);
        $data = $this->validateQuestion($request);

        return $this->guard(fn () => $this->responseSuccess($this->questions->create($exam, $data), 'Question added.'));
    }

    public function updateQuestion(Request $request, int $questionId): JsonResponse
    {
        $this->authorizeAction('exam_module_manage_online_questions');
        $question = $this->findQuestion($questionId);
        $data = $this->validateQuestion($request, true);

        return $this->guard(fn () => $this->responseSuccess($this->questions->update($question, $data), 'Question updated.'));
    }

    public function destroyQuestion(int $questionId): JsonResponse
    {
        $this->authorizeAction('exam_module_manage_online_questions');
        $question = $this->findQuestion($questionId);

        return $this->guard(function () use ($question) {
            $this->questions->delete($question);

            return $this->responseSuccess([], 'Question deleted.');
        });
    }

    public function addFromBank(Request $request, int $id): JsonResponse
    {
        $this->authorizeAction('exam_module_manage_online_questions');
        $exam = $this->find($id);
        $data = $request->validate([
            'question_ids' => 'required|array|min:1|max:500',
            'question_ids.*' => 'integer',
            'pool_id' => 'nullable|integer',
            'marks' => 'nullable|numeric|min:0',
        ]);

        return $this->guard(fn () => $this->responseSuccess(
            $this->questions->addFromBank($exam, $data['question_ids'], $data['pool_id'] ?? null, isset($data['marks']) ? (float) $data['marks'] : null),
            'Questions added from the question bank.'
        ));
    }

    public function addFromBankByCriteria(Request $request, int $id): JsonResponse
    {
        $this->authorizeAction('exam_module_manage_online_questions');
        $exam = $this->find($id);
        $data = $request->validate([
            'limit' => 'required|integer|min:1|max:500',
            'pool_id' => 'nullable|integer',
            'marks' => 'nullable|numeric|min:0',
            'question_bank_subject_id' => 'nullable|integer|tenant_exists:question_bank_subjects,id',
            'question_bank_chapter_id' => 'nullable|integer|tenant_exists:question_bank_chapters,id',
            'question_category_id' => 'nullable|integer|tenant_exists:question_categories,id',
            'question_bank_difficulty_level_id' => 'nullable|integer|tenant_exists:question_bank_difficulty_levels,id',
            'topic_ids' => 'nullable|array',
            'topic_ids.*' => 'integer|tenant_exists:question_bank_topics,id',
            'types' => 'nullable|array',
            'types.*' => ['string', Rule::in(OnlineExamQuestion::TYPES)],
        ]);

        return $this->guard(fn () => $this->responseSuccess(
            $this->questions->addFromBankByCriteria(
                $exam,
                $data,
                (int) $data['limit'],
                $data['pool_id'] ?? null,
                isset($data['marks']) ? (float) $data['marks'] : null
            ),
            'Questions added from the question bank.'
        ));
    }

    // ── Results & grading ───────────────────────────────────────────────────

    public function results(int $id): JsonResponse
    {
        $exam = $this->find($id);

        return $this->responseSuccess($this->reports->roster($exam), 'Online exam results fetched.');
    }

    public function attempt(int $id, int $attemptId): JsonResponse
    {
        $exam = $this->find($id);
        $attempt = OnlineExamAttempt::where('online_exam_id', $exam->id)->findOrFail($attemptId);

        return $this->responseSuccess($this->reports->attemptDetail($attempt, true), 'Attempt fetched.');
    }

    public function gradingQueue(int $id): JsonResponse
    {
        $this->authorizeAction('exam_module_grade_online_answers');
        $exam = $this->find($id);

        $rows = OnlineExamAttemptQuestion::with(['question:id,type,question_text,correct_answer,explanation', 'attempt:id,student_id,attempt_no,submitted_at', 'attempt.student:id,first_name,last_name'])
            ->whereHas('attempt', fn ($q) => $q->where('online_exam_id', $exam->id))
            ->where('needs_grading', true)
            ->orderBy('attempt_id')
            ->orderBy('display_order')
            ->get()
            ->map(fn (OnlineExamAttemptQuestion $row) => [
                'attempt_question_id' => $row->id,
                'attempt_id' => $row->attempt_id,
                'student_id' => $row->attempt?->student_id,
                'student_name' => trim(($row->attempt?->student?->first_name ?? '').' '.($row->attempt?->student?->last_name ?? '')),
                'number' => $row->display_order,
                'type' => $row->question?->type,
                'question_text' => $row->question?->question_text,
                'model_answer' => $row->question?->correct_answer,
                'answer' => $row->answer,
                'max_marks' => (float) $row->max_marks,
            ]);

        return $this->responseSuccess($rows, 'Answers awaiting grading fetched.');
    }

    public function grade(Request $request, int $attemptId): JsonResponse
    {
        $this->authorizeAction('exam_module_grade_online_answers');
        $attempt = OnlineExamAttempt::findOrFail($attemptId);
        abort_unless($this->access->canManage($attempt->onlineExam), 403);

        $data = $request->validate([
            'grades' => 'required|array|min:1',
            'grades.*.attempt_question_id' => 'required|integer',
            'grades.*.marks' => 'required|numeric|min:0',
            'grades.*.feedback' => 'nullable|string|max:2000',
        ]);

        return $this->guard(fn () => $this->responseSuccess(
            $this->scoring->grade($attempt, $data['grades'], auth()->id()),
            'Grades saved.'
        ));
    }

    public function transferPreview(Request $request, int $id): JsonResponse
    {
        $this->authorizeAction('exam_module_transfer_online_marks');
        $exam = $this->find($id);

        return $this->responseSuccess(
            $this->transfers->preview($exam, array_map('intval', (array) $request->input('student_ids', []))),
            'Transfer preview generated.'
        );
    }

    public function transfer(Request $request, int $id): JsonResponse
    {
        $this->authorizeAction('exam_module_transfer_online_marks');
        $exam = $this->find($id);
        $request->validate(['policy' => 'nullable|in:skip,update,ask', 'student_ids' => 'nullable|array']);

        return $this->responseSuccess(
            $this->transfers->transfer($exam, array_map('intval', (array) $request->input('student_ids', [])), $request->input('policy'), auth()->id()),
            'Online marks transferred.'
        );
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    private function find(int $id): OnlineExam
    {
        $exam = OnlineExam::findOrFail($id);
        abort_unless($this->access->canManage($exam), 403);

        return $exam;
    }

    private function findPool(int $poolId): OnlineExamQuestionPool
    {
        $pool = OnlineExamQuestionPool::findOrFail($poolId);
        abort_unless($this->access->canManage($pool->onlineExam), 403);

        return $pool;
    }

    private function findQuestion(int $questionId): OnlineExamQuestion
    {
        $question = OnlineExamQuestion::findOrFail($questionId);
        abort_unless($this->access->canManage($question->onlineExam), 403);

        return $question;
    }

    private function authorizeAction(string $permission): void
    {
        abort_unless($this->access->can($permission) || can_access_system('exam_module_exam_start_up'), 403);
    }

    private function guard(callable $callback): JsonResponse
    {
        try {
            return $callback();
        } catch (OnlineExamException $e) {
            return $this->responseError([], $e->getMessage(), 422);
        }
    }

    private function validateExam(Request $request, ?OnlineExam $exam = null): array
    {
        $partial = $exam !== null;
        $req = fn (string $rule) => ($partial ? 'sometimes|' : '').$rule;

        $data = $request->validate([
            'name' => $req('required|string|max:180'),
            'instructions' => 'nullable|string|max:5000',
            'exam_id' => [$partial ? 'sometimes' : 'required', 'integer', $this->academicContext->tenantExists('exams')],
            'class_id' => $req('required|integer'),
            'section_id' => 'nullable|integer',
            'group_id' => 'nullable|integer',
            'subject_id' => [$partial ? 'sometimes' : 'required', 'integer', $this->academicContext->tenantExists('subjects')],
            'duration_minutes' => $req('required|integer|min:1|max:1440'),
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after:start_at',
            'pass_marks' => 'nullable|numeric|min:0',
            'attempt_limit' => $req('required|integer|min:1|max:20'),
            'score_policy' => ['nullable', Rule::in([OnlineExam::SCORE_LATEST, OnlineExam::SCORE_BEST, OnlineExam::SCORE_FIRST])],
            'result_visibility' => ['nullable', Rule::in([OnlineExam::RESULT_IMMEDIATE, OnlineExam::RESULT_AFTER_END, OnlineExam::RESULT_HIDDEN])],
            'questions_per_attempt' => 'nullable|integer|min:1|max:500',
            'negative_marking' => 'nullable|boolean',
            'negative_marks_per_wrong' => 'nullable|numeric|min:0',
            'random_questions' => 'nullable|boolean',
            'random_options' => 'nullable|boolean',
            'conversion_enabled' => 'nullable|boolean',
            'online_full_marks' => 'nullable|numeric|min:0',
            'academic_full_marks' => 'nullable|numeric|min:0',
            'transfer_policy' => $req('required|in:skip,update,ask'),
            'mark_component_slot' => 'nullable|integer|min:1|max:6',
        ]);

        $classId = (int) ($data['class_id'] ?? $exam?->class_id);
        $sectionId = array_key_exists('section_id', $data) ? ($data['section_id'] ?: null) : $exam?->section_id;
        $groupId = array_key_exists('group_id', $data) ? ($data['group_id'] ?: null) : $exam?->group_id;
        $subjectId = (int) ($data['subject_id'] ?? $exam?->subject_id);

        $this->academicContext->assertClassSectionGroup($classId, $sectionId ? (int) $sectionId : null, $groupId ? (int) $groupId : null);

        if (! Subject::where('id', $subjectId)->where('class_id', $classId)->exists()) {
            abort(response()->json(['message' => _lang('The selected subject does not belong to the chosen class.'), 'errors' => ['subject_id' => ['invalid']]], 422));
        }

        if (! $this->access->canManageContext($classId, $subjectId, $sectionId ? (int) $sectionId : null)) {
            abort(response()->json(['status' => false, 'message' => _lang('You do not teach this subject for the selected class/section.')], 403));
        }

        return $data;
    }

    private function validateQuestion(Request $request, bool $partial = false): array
    {
        $req = fn (string $rule) => ($partial ? 'sometimes|' : '').$rule;

        return $request->validate([
            'type' => [$partial ? 'sometimes' : 'required', Rule::in(OnlineExamQuestion::TYPES)],
            'question_text' => $req('required|string|max:20000'),
            'image' => 'nullable|string|max:255',
            'options' => 'nullable|array|max:20',
            'options.*' => 'nullable|string|max:2000',
            'correct_answer' => 'nullable|array|max:20',
            'correct_answer.*' => 'nullable|string|max:2000',
            'explanation' => 'nullable|string|max:5000',
            'marks' => $req('required|numeric|min:0|max:1000'),
            'difficulty' => 'nullable|string|max:30',
            'pool_id' => 'nullable|integer',
            'display_order' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);
    }
}
