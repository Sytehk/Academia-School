<?php

namespace Modules\Examination\Services;

use Illuminate\Support\Facades\DB;
use Modules\Examination\Models\ExamMark;
use Modules\Examination\Models\OnlineExam;
use Modules\Examination\Models\OnlineExamAttempt;
use Modules\Examination\Models\OnlineExamMarkTransfer;

class OnlineExamResultTransferService
{
    public function __construct(
        private readonly MarkEntryService $markEntryService,
        private readonly OnlineExamScoringService $scoringService,
        private readonly ExamContextService $examContextService,
    ) {}

    public function preview(OnlineExam $onlineExam, array $studentIds = []): array
    {
        // One row per student: the attempt that counts under the exam's score
        // policy (latest / best / first). Attempts still waiting for manual
        // grading are not final and cannot be transferred yet.
        $attempts = OnlineExamAttempt::with('student')
            ->where('online_exam_id', $onlineExam->id)
            ->whereIn('status', [OnlineExamAttempt::STATUS_SUBMITTED, OnlineExamAttempt::STATUS_AUTO_SUBMITTED])
            ->when($studentIds, fn ($query) => $query->whereIn('student_id', $studentIds))
            ->orderBy('id')
            ->get()
            ->groupBy('student_id')
            ->map(fn ($mine) => $this->scoringService->pickCounted($mine, (string) $onlineExam->score_policy))
            ->filter()
            ->values();

        $sessionId = $onlineExam->session_id ?: $this->examContextService->currentSessionId();
        $studentIdsList = $attempts->pluck('student_id')->all() ?: [0];

        $existingMarks = ExamMark::where('exam_id', $onlineExam->exam_id)
            ->where('subject_id', $onlineExam->subject_id)
            ->whereIn('student_id', $studentIdsList)
            ->when($sessionId, fn ($query) => $query->where('session_id', $sessionId))
            ->get()
            ->keyBy('student_id');

        $transfers = OnlineExamMarkTransfer::where('online_exam_id', $onlineExam->id)
            ->get()
            ->keyBy('student_id');

        $configuredOnlineFull = (float) $onlineExam->online_full_marks;
        $academicFull = (float) ($onlineExam->academic_full_marks ?: $this->markEntryService->resolveMaxMarks(
            (int) $onlineExam->subject_id,
            (int) $onlineExam->exam_id
        ));

        return $attempts->map(function (OnlineExamAttempt $attempt) use ($onlineExam, $existingMarks, $transfers, $configuredOnlineFull, $academicFull, $sessionId) {
            // Each student may have been dealt a different paper (pools / random N).
            $onlineFull = $configuredOnlineFull ?: (float) ($attempt->max_score ?: $onlineExam->total_marks);

            $session = $this->examContextService->resolveStudentSession(
                (int) $attempt->student_id,
                (int) $onlineExam->class_id,
                $sessionId,
                $onlineExam->section_id ? (int) $onlineExam->section_id : null,
                $this->examContextService->resolveGroupId(
                    $onlineExam->group_id ? (int) $onlineExam->group_id : null
                ),
            );

            $transferred = $this->scoringService->convertScore(
                (float) $attempt->score,
                $onlineFull,
                $academicFull,
                (bool) $onlineExam->conversion_enabled
            );

            $existing = $existingMarks->get($attempt->student_id);
            $previous = $transfers->get($attempt->student_id);

            return [
                'student_id' => $attempt->student_id,
                'student_name' => trim(($attempt->student->first_name ?? '').' '.($attempt->student->last_name ?? '')),
                'roll' => $session?->roll,
                'group_id' => $session?->group_id,
                'group_name' => $session?->group?->group_name,
                'section_name' => $session?->section?->section_name,
                'session_found' => (bool) $session,
                'attempt_id' => $attempt->id,
                'subject_id' => $onlineExam->subject_id,
                'subject_name' => $onlineExam->subject?->subject_name,
                'online_score' => (float) $attempt->score,
                'online_full_marks' => $onlineFull,
                'converted' => (bool) $onlineExam->conversion_enabled,
                'transferred_marks' => $transferred,
                'academic_full_marks' => $academicFull,
                'existing_mark' => $existing?->total_marks,
                'already_transferred' => (bool) $previous,
                'last_action' => $previous?->action,
                'last_transferred_at' => $previous?->transferred_at,
            ];
        })->all();
    }

    /**
     * @param  string  $policy  skip|update|ask
     */
    public function transfer(OnlineExam $onlineExam, array $studentIds = [], ?string $policy = null, ?int $transferredBy = null): array
    {
        $policy = $policy ?: $onlineExam->transfer_policy;
        $preview = $this->preview($onlineExam, $studentIds);
        $results = [];
        $slot = max(1, min(6, (int) $onlineExam->mark_component_slot));
        $sessionId = $onlineExam->session_id ?: $this->examContextService->currentSessionId();

        DB::beginTransaction();
        try {
            foreach ($preview as $row) {
                if (! $row['session_found']) {
                    $results[] = array_merge($row, [
                        'action' => 'failed',
                        'message' => 'Student session not found for this class/section/group.',
                    ]);

                    continue;
                }

                if ($policy === OnlineExam::POLICY_ASK && $row['existing_mark'] !== null) {
                    $results[] = array_merge($row, [
                        'action' => OnlineExamMarkTransfer::ACTION_SKIPPED,
                        'message' => 'Existing mark found. Confirm skip or update.',
                    ]);

                    continue;
                }

                if ($row['existing_mark'] !== null && $policy === OnlineExam::POLICY_SKIP) {
                    $this->recordTransfer($onlineExam, $row, OnlineExamMarkTransfer::ACTION_SKIPPED, $transferredBy, null);
                    $results[] = array_merge($row, [
                        'action' => OnlineExamMarkTransfer::ACTION_SKIPPED,
                        'message' => 'Skipped because a mark already exists.',
                    ]);

                    continue;
                }

                $session = $this->examContextService->resolveStudentSession(
                    (int) $row['student_id'],
                    (int) $onlineExam->class_id,
                    $sessionId,
                    $onlineExam->section_id ? (int) $onlineExam->section_id : null,
                    $this->examContextService->resolveGroupId(
                        $onlineExam->group_id ? (int) $onlineExam->group_id : null,
                        null
                    ),
                );

                if (! $session) {
                    $results[] = array_merge($row, [
                        'action' => 'failed',
                        'message' => 'Student session not found for this class.',
                    ]);

                    continue;
                }

                $identifiers = [
                    'institute_id' => $onlineExam->institute_id,
                    'branch_id' => $onlineExam->branch_id,
                    'session_id' => $sessionId ?: $session->session_id,
                    'student_id' => $row['student_id'],
                    'class_id' => $onlineExam->class_id,
                    'group_id' => $this->examContextService->resolveGroupId(
                        $onlineExam->group_id ? (int) $onlineExam->group_id : null,
                        $session
                    ),
                    'subject_id' => $onlineExam->subject_id,
                    'exam_id' => $onlineExam->exam_id,
                ];

                $existingMark = $this->examContextService->findExamMark($identifiers);
                $markValues = [];
                if ($existingMark && $policy === OnlineExam::POLICY_UPDATE) {
                    for ($i = 1; $i <= 6; $i++) {
                        $markValues[$i] = (float) $existingMark->{"mark{$i}"};
                    }
                }
                $markValues[$slot] = $row['transferred_marks'];
                $examMark = $this->markEntryService->saveSectionWiseMarks($identifiers, $markValues, [
                    'source' => ExamMark::SOURCE_ONLINE,
                ]);

                $action = $row['existing_mark'] !== null
                    ? OnlineExamMarkTransfer::ACTION_UPDATED
                    : OnlineExamMarkTransfer::ACTION_CREATED;

                $this->recordTransfer($onlineExam, $row, $action, $transferredBy, $examMark->id);
                $results[] = array_merge($row, [
                    'action' => $action,
                    'exam_mark_id' => $examMark->id,
                    'message' => $action === OnlineExamMarkTransfer::ACTION_UPDATED
                        ? 'Existing mark updated from online score.'
                        : 'Online score transferred into mark input.',
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    private function recordTransfer(OnlineExam $onlineExam, array $row, string $action, ?int $transferredBy, ?int $examMarkId): void
    {
        OnlineExamMarkTransfer::updateOrCreate(
            [
                'online_exam_id' => $onlineExam->id,
                'student_id' => $row['student_id'],
            ],
            [
                'institute_id' => $onlineExam->institute_id,
                'branch_id' => $onlineExam->branch_id,
                'attempt_id' => $row['attempt_id'],
                'exam_mark_id' => $examMarkId,
                'online_score' => $row['online_score'],
                'online_full_marks' => $row['online_full_marks'],
                'transferred_marks' => $row['transferred_marks'],
                'academic_full_marks' => $row['academic_full_marks'],
                'converted' => $row['converted'],
                'action' => $action,
                'transferred_by' => $transferredBy,
                'transferred_at' => now(),
            ]
        );
    }
}
