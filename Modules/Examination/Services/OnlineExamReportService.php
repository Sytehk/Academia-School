<?php

namespace Modules\Examination\Services;

use App\Services\AcademicContextService;
use Modules\Academic\Models\StudentSession;
use Modules\Examination\Models\OnlineExam;
use Modules\Examination\Models\OnlineExamAttempt;
use Modules\Examination\Models\OnlineExamAttemptQuestion;

/**
 * Read models for online exam results: the class roster with every student's
 * counted score, and a single attempt's question-by-question breakdown.
 */
class OnlineExamReportService
{
    public function __construct(
        private readonly OnlineExamScoringService $scoring,
        private readonly AcademicContextService $academicContext,
    ) {}

    /**
     * @return array{summary: array<string, mixed>, students: array<int, array<string, mixed>>}
     */
    public function roster(OnlineExam $exam): array
    {
        $sessions = $this->academicContext
            ->studentSessionQuery((int) $exam->class_id, $exam->section_id ? (int) $exam->section_id : null, $exam->group_id ? (int) $exam->group_id : null)
            ->with('student:id,first_name,last_name,registration_no')
            ->orderByRaw('CAST(roll AS UNSIGNED), roll')
            ->get();

        $attempts = OnlineExamAttempt::where('online_exam_id', $exam->id)->orderBy('id')->get()->groupBy('student_id');

        $rows = $sessions->map(function (StudentSession $session) use ($exam, $attempts) {
            $mine = $attempts->get($session->student_id, collect());
            $counted = $this->scoring->pickCounted($mine, (string) $exam->score_policy);

            $state = match (true) {
                $mine->isEmpty() => 'not_attempted',
                $mine->contains(fn ($a) => $a->status === OnlineExamAttempt::STATUS_STARTED) => 'in_progress',
                $mine->contains(fn ($a) => $a->grading_status === OnlineExamAttempt::GRADING_PENDING) && ! $counted => 'pending_grading',
                default => 'completed',
            };

            return [
                'student_id' => $session->student_id,
                'student_session_id' => $session->id,
                'roll' => $session->roll,
                'student_name' => trim(($session->student->first_name ?? '').' '.($session->student->last_name ?? '')),
                'registration_no' => $session->student?->registration_no,
                'state' => $state,
                'attempts_used' => $mine->count(),
                'pending_grading' => $mine->contains(fn ($a) => $a->grading_status === OnlineExamAttempt::GRADING_PENDING),
                'counted_attempt_id' => $counted?->id,
                'score' => $counted ? (float) $counted->score : null,
                'max_score' => $counted ? (float) $counted->max_score : null,
                'converted_score' => $counted ? (float) $counted->converted_score : null,
                'is_passed' => $counted?->is_passed,
                'submitted_at' => $counted?->submitted_at,
            ];
        })->values();

        $scores = $rows->pluck('score')->filter(fn ($s) => $s !== null);

        return [
            'summary' => [
                'enrolled' => $rows->count(),
                'attempted' => $rows->where('state', '!=', 'not_attempted')->count(),
                'completed' => $scores->count(),
                'pending_grading' => $rows->where('pending_grading', true)->count(),
                'passed' => $rows->where('is_passed', true)->count(),
                'average' => $scores->isNotEmpty() ? round($scores->avg(), 2) : null,
                'highest' => $scores->max(),
                'lowest' => $scores->min(),
                'score_policy' => $exam->score_policy,
            ],
            'students' => $rows->all(),
        ];
    }

    /**
     * @param  bool  $revealKey  include correct answers and explanations
     */
    public function attemptDetail(OnlineExamAttempt $attempt, bool $revealKey): array
    {
        $rows = $attempt->attemptQuestions()->with('question')->get();

        return [
            'attempt' => [
                'id' => $attempt->id,
                'online_exam_id' => $attempt->online_exam_id,
                'student_id' => $attempt->student_id,
                'attempt_no' => $attempt->attempt_no,
                'status' => $attempt->status,
                'grading_status' => $attempt->grading_status,
                'score' => (float) $attempt->score,
                'max_score' => (float) $attempt->max_score,
                'converted_score' => $attempt->converted_score !== null ? (float) $attempt->converted_score : null,
                'is_passed' => (bool) $attempt->is_passed,
                'correct_count' => $attempt->correct_count,
                'incorrect_count' => $attempt->incorrect_count,
                'skipped_count' => $attempt->skipped_count,
                'started_at' => $attempt->started_at,
                'submitted_at' => $attempt->submitted_at,
                'time_taken' => $attempt->time_taken,
            ],
            'questions' => $rows->map(function (OnlineExamAttemptQuestion $row) use ($revealKey) {
                $q = $row->question;

                return array_filter([
                    'attempt_question_id' => $row->id,
                    'number' => $row->display_order,
                    'type' => $q?->type,
                    'question_text' => $q?->question_text,
                    'options' => $q?->options,
                    'your_answer' => $row->answer,
                    'max_marks' => (float) $row->max_marks,
                    'awarded_marks' => $row->awarded_marks !== null ? (float) $row->awarded_marks : null,
                    'is_correct' => $row->is_correct,
                    'needs_grading' => $row->needs_grading,
                    'feedback' => $row->feedback,
                    'correct_answer' => $revealKey ? $q?->correct_answer : null,
                    'explanation' => $revealKey ? $q?->explanation : null,
                ], fn ($v, $k) => $revealKey || ! in_array($k, ['correct_answer', 'explanation'], true), ARRAY_FILTER_USE_BOTH);
            })->values()->all(),
        ];
    }
}
