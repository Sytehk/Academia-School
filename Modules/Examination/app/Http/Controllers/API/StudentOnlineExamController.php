<?php

namespace Modules\Examination\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\StudentSession;
use Modules\Examination\Exceptions\OnlineExamException;
use Modules\Examination\Models\OnlineExam;
use Modules\Examination\Models\OnlineExamAttempt;
use Modules\Examination\Services\OnlineExamReportService;
use Modules\Examination\Services\OnlineExamScoringService;

/**
 * Student-portal CBT API: list available exams, start/resume an attempt, save
 * answers as the student goes, submit, review attempt history and results.
 */
class StudentOnlineExamController extends Controller
{
    public function __construct(
        private readonly OnlineExamScoringService $scoring,
        private readonly OnlineExamReportService $reports,
        private readonly AcademicContextService $academicContext,
    ) {}

    public function index(): JsonResponse
    {
        [$student, $session] = $this->context();
        if (! $session) {
            return $this->responseSuccess([], 'No student session found.');
        }

        $exams = OnlineExam::with(['subject:id,subject_name', 'exam:id,name'])
            ->where('status', '!=', OnlineExam::STATUS_DRAFT)
            ->where('is_active', true)
            ->where('class_id', $session->class_id)
            ->where(fn ($q) => $q->whereNull('section_id')->orWhere('section_id', $session->section_id))
            ->where(fn ($q) => $q->whereNull('group_id')->orWhere('group_id', $session->group_id))
            ->orderByDesc('start_at')
            ->get();

        $attempts = OnlineExamAttempt::where('student_id', $student->id)
            ->whereIn('online_exam_id', $exams->pluck('id'))
            ->get()
            ->groupBy('online_exam_id');

        return $this->responseSuccess($exams->map(function (OnlineExam $exam) use ($attempts) {
            $mine = $attempts->get($exam->id, collect());
            $running = $mine->first(fn ($a) => $a->status === OnlineExamAttempt::STATUS_STARTED && ! $a->isExpired());
            $submitted = $mine->filter(fn ($a) => $a->isSubmitted())->count();
            $counted = $exam->resultVisible() ? $this->scoring->pickCounted($mine, (string) $exam->score_policy) : null;

            return [
                'id' => $exam->id,
                'name' => $exam->name,
                'instructions' => $exam->instructions,
                'subject' => $exam->subject?->subject_name,
                'term' => $exam->exam?->name,
                'duration_minutes' => $exam->duration_minutes,
                'start_at' => $exam->start_at,
                'end_at' => $exam->end_at,
                'total_questions' => $exam->total_questions,
                'total_marks' => (float) $exam->total_marks,
                'state' => $exam->hasEnded() ? 'ended' : ($exam->isOpen() ? 'open' : 'upcoming'),
                'attempt_limit' => $exam->attempt_limit,
                'attempts_used' => $submitted,
                'running_attempt_id' => $running?->id,
                'can_start' => $exam->isOpen() && ($running || $submitted < $exam->attempt_limit),
                'score' => $counted ? (float) $counted->score : null,
                'is_passed' => $counted?->is_passed,
            ];
        })->values(), 'Available online exams fetched.');
    }

    public function start(int $id): JsonResponse
    {
        [$student, $session] = $this->context();
        abort_if(! $student, 403);

        $exam = OnlineExam::findOrFail($id);
        if (! $this->scoring->isEligible($exam, $session)) {
            return $this->responseError([], _lang('This online exam is not assigned to your class.'), 403);
        }

        try {
            $attempt = $this->scoring->startAttempt($exam, (int) $student->id, auth()->id());
        } catch (OnlineExamException $e) {
            return $this->responseError([], $e->getMessage(), 422);
        }

        return $this->responseSuccess($this->paper($attempt), 'Exam attempt started.');
    }

    public function attempts(): JsonResponse
    {
        [$student] = $this->context();
        abort_if(! $student, 403);

        $attempts = OnlineExamAttempt::with('onlineExam:id,name,result_visibility,status,end_at,subject_id')
            ->where('student_id', $student->id)
            ->latest('id')
            ->get()
            ->map(function (OnlineExamAttempt $a) {
                $visible = $a->isSubmitted() && $a->onlineExam?->resultVisible();

                return [
                    'id' => $a->id,
                    'online_exam_id' => $a->online_exam_id,
                    'exam_name' => $a->onlineExam?->name,
                    'attempt_no' => $a->attempt_no,
                    'status' => $a->status,
                    'grading_status' => $a->grading_status,
                    'started_at' => $a->started_at,
                    'submitted_at' => $a->submitted_at,
                    'score' => $visible ? (float) $a->score : null,
                    'max_score' => (float) $a->max_score,
                    'is_passed' => $visible && $a->isFinal() ? $a->is_passed : null,
                ];
            });

        return $this->responseSuccess($attempts, 'Attempt history fetched.');
    }

    public function show(int $attemptId): JsonResponse
    {
        $attempt = $this->ownedAttempt($attemptId);

        if ($attempt->status === OnlineExamAttempt::STATUS_STARTED && $attempt->isExpired()) {
            $attempt = $this->scoring->submit($attempt, $attempt->answers ?? [], true);
        }

        if ($attempt->isSubmitted()) {
            return $this->result($attemptId);
        }

        return $this->responseSuccess($this->paper($attempt), 'Attempt fetched.');
    }

    public function save(Request $request, int $attemptId): JsonResponse
    {
        $attempt = $this->ownedAttempt($attemptId);
        $request->validate(['answers' => 'present|array']);

        try {
            $attempt = $this->scoring->saveAnswers($attempt, $request->input('answers', []));
        } catch (OnlineExamException $e) {
            return $this->responseError([], $e->getMessage(), 422);
        }

        return $this->responseSuccess([
            'status' => $attempt->status,
            'expires_at' => optional($attempt->expires_at)->toIso8601String(),
            'server_now' => now()->toIso8601String(),
        ], $attempt->isSubmitted() ? 'Time is up — your exam was submitted.' : 'Answers saved.');
    }

    public function submit(Request $request, int $attemptId): JsonResponse
    {
        $attempt = $this->ownedAttempt($attemptId);

        try {
            $attempt = $this->scoring->submit($attempt, $request->input('answers', $attempt->answers ?? []));
        } catch (OnlineExamException $e) {
            return $this->responseError([], $e->getMessage(), 422);
        }

        return $this->responseSuccess($this->resultPayload($attempt), 'Exam submitted.');
    }

    public function result(int $attemptId): JsonResponse
    {
        $attempt = $this->ownedAttempt($attemptId);
        if (! $attempt->isSubmitted()) {
            return $this->responseError([], _lang('This attempt has not been submitted yet.'), 422);
        }

        return $this->responseSuccess($this->resultPayload($attempt), 'Attempt result fetched.');
    }

    private function resultPayload(OnlineExamAttempt $attempt): array
    {
        $exam = $attempt->onlineExam()->first();

        if (! $exam->resultVisible()) {
            return [
                'attempt_id' => $attempt->id,
                'status' => $attempt->status,
                'result_released' => false,
                'message' => _lang('Your answers were submitted. The result will be released by your teacher.'),
            ];
        }

        $detail = $this->reports->attemptDetail($attempt, $exam->answersRevealable());

        return $detail + [
            'result_released' => true,
            'answers_revealed' => $exam->answersRevealable(),
            'pending_grading' => $attempt->grading_status === OnlineExamAttempt::GRADING_PENDING,
        ];
    }

    private function paper(OnlineExamAttempt $attempt): array
    {
        $exam = $attempt->onlineExam()->first();

        return [
            'attempt' => [
                'id' => $attempt->id,
                'attempt_no' => $attempt->attempt_no,
                'status' => $attempt->status,
                'started_at' => $attempt->started_at,
            ],
            'exam' => [
                'id' => $exam->id,
                'name' => $exam->name,
                'instructions' => $exam->instructions,
                'negative_marking' => (bool) $exam->negative_marking,
                'negative_marks_per_wrong' => (float) $exam->negative_marks_per_wrong,
            ],
            'max_score' => (float) $attempt->fresh()->max_score,
            'questions' => $this->scoring->questionsForAttempt($attempt),
            'server_now' => now()->toIso8601String(),
            'expires_at' => optional($attempt->expires_at)->toIso8601String(),
            'remaining_seconds' => $attempt->expires_at ? max(0, now()->diffInSeconds($attempt->expires_at, false)) : null,
        ];
    }

    /**
     * @return array{0: ?Student, 1: ?StudentSession}
     */
    private function context(): array
    {
        $student = Student::where('user_id', auth()->id())->first();
        $session = $student ? $this->academicContext->studentSessionForStudent((int) $student->id) : null;

        return [$student, $session];
    }

    private function ownedAttempt(int $attemptId): OnlineExamAttempt
    {
        [$student] = $this->context();
        abort_if(! $student, 403);

        $attempt = OnlineExamAttempt::findOrFail($attemptId);
        abort_unless((int) $attempt->student_id === (int) $student->id, 403);

        return $attempt;
    }
}
