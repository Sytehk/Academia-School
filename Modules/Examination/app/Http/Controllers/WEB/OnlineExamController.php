<?php

namespace Modules\Examination\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Academic\Models\Exam;
use Modules\Academic\Models\Subject;
use Modules\Academic\Services\ClassService;
use Modules\Examination\Exceptions\OnlineExamException;
use Modules\Examination\Models\OnlineExam;
use Modules\Examination\Models\OnlineExamAttempt;
use Modules\Examination\Models\OnlineExamQuestion;
use Modules\Examination\Services\OnlineExamQuestionService;
use Modules\Examination\Services\OnlineExamResultTransferService;
use Modules\Examination\Services\ResultCardService;

class OnlineExamController extends Controller
{
    public function __construct(
        private readonly OnlineExamQuestionService $questionService,
        private readonly ClassService $classService,
        private readonly OnlineExamResultTransferService $transferService,
        private readonly ResultCardService $resultCardService,
        private readonly AcademicContextService $academicContext,
    ) {}

    public function index(): Factory|View
    {
        $this->authorizeManage();
        $exams = OnlineExam::with(['exam', 'class', 'subject', 'section'])
            ->latest()
            ->get();

        return view('examination::online_exams.index', compact('exams'));
    }

    public function create(): Factory|View
    {
        $this->authorizeManage();

        return view('examination::online_exams.form', [
            'onlineExam' => new OnlineExam,
            'classes' => $this->classService->getClasses(),
            'exams' => Exam::orderBy('name')->get(['id', 'name']),
            'subjects' => Subject::orderBy('subject_name')->get(['id', 'subject_name', 'class_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManage();
        $data = $this->validated($request);
        // The web form has a single "active" switch: active means published.
        OnlineExam::create($data + [
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'session_id' => get_option('academic_year') ?: null,
            'status' => $data['is_active'] ? OnlineExam::STATUS_PUBLISHED : OnlineExam::STATUS_DRAFT,
            'published_at' => $data['is_active'] ? now() : null,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('online-exams.index')->with('success', _lang('Online exam created.'));
    }

    public function edit(int $id): Factory|View
    {
        $this->authorizeManage();
        $onlineExam = OnlineExam::with('questions')->findOrFail($id);

        return view('examination::online_exams.form', [
            'onlineExam' => $onlineExam,
            'classes' => $this->classService->getClasses(),
            'exams' => Exam::orderBy('name')->get(['id', 'name']),
            'subjects' => Subject::orderBy('subject_name')->get(['id', 'subject_name', 'class_id']),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorizeManage();
        $onlineExam = OnlineExam::findOrFail($id);
        $data = $this->validated($request);
        if ($data['is_active'] && $onlineExam->status === OnlineExam::STATUS_DRAFT) {
            $data += ['status' => OnlineExam::STATUS_PUBLISHED, 'published_at' => $onlineExam->published_at ?? now()];
        } elseif (! $data['is_active'] && $onlineExam->status === OnlineExam::STATUS_PUBLISHED && ! $this->questionService->isLocked($onlineExam)) {
            $data += ['status' => OnlineExam::STATUS_DRAFT];
        }
        $onlineExam->update($data);
        $this->questionService->refreshTotals($onlineExam);

        return back()->with('success', _lang('Online exam updated.'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeManage();
        $onlineExam = OnlineExam::findOrFail($id);
        if ($this->questionService->isLocked($onlineExam)) {
            return back()->with('error', _lang('This exam has attempts and cannot be deleted. Deactivate it instead.'));
        }
        $onlineExam->delete();

        return redirect()->route('online-exams.index')->with('success', _lang('Online exam deleted.'));
    }

    public function storeQuestion(Request $request, int $id): RedirectResponse
    {
        $this->authorizeQuestions();
        $onlineExam = OnlineExam::findOrFail($id);
        $data = $request->validate([
            'type' => 'required|in:multiple_choice,multiple_select,true_false,fill_blank,short_answer,descriptive',
            'question_text' => 'required|string',
            'options' => 'nullable|string',
            'correct_answer' => 'nullable|string',
            'explanation' => 'nullable|string',
            'marks' => 'required|numeric|min:0',
            'difficulty' => 'nullable|string|max:30',
        ]);

        try {
            $this->questionService->create($onlineExam, [
                'type' => $data['type'],
                'question_text' => $data['question_text'],
                'options' => $this->csvToArray($data['options'] ?? ''),
                'correct_answer' => $this->csvToArray($data['correct_answer'] ?? ''),
                'explanation' => $data['explanation'] ?? null,
                'marks' => $data['marks'],
                'difficulty' => $data['difficulty'] ?? null,
            ]);
        } catch (OnlineExamException $e) {
            return back()->withInput()->with('error', _lang($e->getMessage()));
        }

        return back()->with('success', _lang('Question added.'));
    }

    public function destroyQuestion(int $id): RedirectResponse
    {
        $this->authorizeQuestions();
        $question = OnlineExamQuestion::findOrFail($id);

        try {
            $this->questionService->delete($question);
        } catch (OnlineExamException $e) {
            return back()->with('error', _lang($e->getMessage()));
        }

        return back()->with('success', _lang('Question deleted.'));
    }

    public function results(int $id): Factory|View
    {
        $this->authorizeManage();
        $onlineExam = OnlineExam::with(['subject', 'exam', 'class'])->findOrFail($id);
        $attempts = OnlineExamAttempt::with('student')
            ->where('online_exam_id', $onlineExam->id)
            ->whereIn('status', [OnlineExamAttempt::STATUS_SUBMITTED, OnlineExamAttempt::STATUS_AUTO_SUBMITTED])
            ->orderByDesc('score')
            ->get();

        return view('examination::online_exams.results', compact('onlineExam', 'attempts'));
    }

    public function resultsPrint(int $id): Factory|View
    {
        $this->authorizeManage();
        $onlineExam = OnlineExam::with(['subject', 'exam', 'class'])->findOrFail($id);
        $attempts = OnlineExamAttempt::with('student')
            ->where('online_exam_id', $onlineExam->id)
            ->whereIn('status', [OnlineExamAttempt::STATUS_SUBMITTED, OnlineExamAttempt::STATUS_AUTO_SUBMITTED])
            ->orderByDesc('score')
            ->get();
        $header = $this->resultCardService->schoolHeader();

        return view('examination::online_exams.results_print', compact('onlineExam', 'attempts', 'header'));
    }

    public function transferPreview(Request $request, int $id): Factory|View
    {
        $this->authorizeTransfer();
        $onlineExam = OnlineExam::with('subject')->findOrFail($id);
        $studentIds = array_filter(array_map('intval', (array) $request->input('student_ids', [])));
        $preview = $this->transferService->preview($onlineExam, $studentIds);

        return view('examination::online_exams.transfer', compact('onlineExam', 'preview'));
    }

    public function transfer(Request $request, int $id): RedirectResponse
    {
        $this->authorizeTransfer();
        $onlineExam = OnlineExam::findOrFail($id);
        $studentIds = array_filter(array_map('intval', (array) $request->input('student_ids', [])));
        $policy = $request->input('policy', $onlineExam->transfer_policy);
        $this->transferService->transfer($onlineExam, $studentIds, $policy, auth()->id());

        return back()->with('success', _lang('Online marks transferred to mark input.'));
    }

    public function transferPreviewPrint(int $id): Factory|View
    {
        $this->authorizeTransfer();
        $onlineExam = OnlineExam::with('subject')->findOrFail($id);
        $preview = $this->transferService->preview($onlineExam);
        $header = $this->resultCardService->schoolHeader();

        return view('examination::online_exams.transfer_print', compact('onlineExam', 'preview', 'header'));
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:180',
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'group_id' => 'nullable|exists:student_groups,id',
            'subject_id' => 'required|exists:subjects,id',
            'duration_minutes' => 'required|integer|min:1',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'pass_marks' => 'nullable|numeric|min:0',
            'attempt_limit' => 'required|integer|min:1',
            'negative_marking' => 'nullable|boolean',
            'negative_marks_per_wrong' => 'nullable|numeric|min:0',
            'random_questions' => 'nullable|boolean',
            'random_options' => 'nullable|boolean',
            'conversion_enabled' => 'nullable|boolean',
            'online_full_marks' => 'nullable|numeric|min:0',
            'academic_full_marks' => 'nullable|numeric|min:0',
            'transfer_policy' => 'required|in:skip,update,ask',
            'mark_component_slot' => 'nullable|integer|min:1|max:6',
            'is_active' => 'nullable|boolean',
        ]) + [
            'negative_marking' => $request->boolean('negative_marking'),
            'random_questions' => $request->boolean('random_questions'),
            'random_options' => $request->boolean('random_options'),
            'conversion_enabled' => $request->boolean('conversion_enabled'),
            'is_active' => $request->boolean('is_active', true),
        ];

        $this->academicContext->assertClassSectionGroup(
            (int) $data['class_id'],
            ! empty($data['section_id']) ? (int) $data['section_id'] : null,
            ! empty($data['group_id']) ? (int) $data['group_id'] : null,
        );

        return $data;
    }

    private function csvToArray(?string $value): array
    {
        if (! $value) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value)), fn ($item) => $item !== ''));
    }

    private function authorizeManage(): void
    {
        abort_unless(auth()->user()?->can('exam_module_manage_online_exams') || auth()->user()?->can('exam_module_exam_start_up'), 403);
    }

    private function authorizeQuestions(): void
    {
        abort_unless(auth()->user()?->can('exam_module_manage_online_questions') || auth()->user()?->can('exam_module_exam_start_up'), 403);
    }

    private function authorizeTransfer(): void
    {
        abort_unless(auth()->user()?->can('exam_module_transfer_online_marks') || auth()->user()?->can('exam_module_mark_input'), 403);
    }
}
