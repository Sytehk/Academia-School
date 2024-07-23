<?php

namespace Modules\Examination\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Examination\Models\ResultPublication;
use Modules\Examination\Models\ResultPublicationLog;

/**
 * Result approval workflow for one exam + class:
 *
 *   draft ──submit──▶ submitted ──approve──▶ approved ──publish──▶ published
 *     ▲                  │  reject                │ reject            │ unpublish
 *     └──────────────────┴────────────────────────┘                   ▼ approved
 *
 * Publishing locks the results; a locked exam + class refuses every mark,
 * assessment and comment change (see assertUnlocked()). Only an unpublished
 * result can be unlocked. Every transition is logged with who, when and why.
 */
class ResultPublicationService
{
    /** action => [allowed from-statuses, to-status, note required] */
    private const TRANSITIONS = [
        'submit' => [[ResultPublication::STATUS_DRAFT], ResultPublication::STATUS_SUBMITTED, false],
        'approve' => [[ResultPublication::STATUS_SUBMITTED], ResultPublication::STATUS_APPROVED, false],
        'reject' => [[ResultPublication::STATUS_SUBMITTED, ResultPublication::STATUS_APPROVED], ResultPublication::STATUS_DRAFT, true],
        'publish' => [[ResultPublication::STATUS_APPROVED], ResultPublication::STATUS_PUBLISHED, false],
        'unpublish' => [[ResultPublication::STATUS_PUBLISHED], ResultPublication::STATUS_APPROVED, true],
    ];

    /** Permission(s) that may perform each action (any of). */
    public const ACTION_PERMISSIONS = [
        'submit' => ['exam_module_mark_input', 'exam_module_result_comments', 'exam_module_approve_result'],
        'approve' => ['exam_module_approve_result'],
        'reject' => ['exam_module_approve_result'],
        'publish' => ['exam_module_publish_result'],
        'unpublish' => ['exam_module_publish_result'],
        'lock' => ['exam_module_approve_result', 'exam_module_publish_result'],
        'unlock' => ['exam_module_publish_result'],
    ];

    /** @var array<string, bool> memoised lock state per tenant|exam|class */
    private array $lockCache = [];

    public function __construct(
        private readonly ExamContextService $examContext,
    ) {}

    public function forExamClass(int $examId, int $classId): ResultPublication
    {
        return ResultPublication::firstOrNew(
            ['exam_id' => $examId, 'class_id' => $classId],
            [
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'session_id' => $this->examContext->currentSessionId(),
                'status' => ResultPublication::STATUS_DRAFT,
                'is_locked' => false,
            ]
        );
    }

    /**
     * @throws ValidationException
     */
    public function perform(string $action, int $examId, int $classId, ?string $note, ?int $userId): ResultPublication
    {
        if (! array_key_exists($action, self::ACTION_PERMISSIONS)) {
            throw ValidationException::withMessages(['action' => ['Unknown result action.']]);
        }

        return DB::transaction(function () use ($action, $examId, $classId, $note, $userId) {
            $publication = $this->forExamClass($examId, $classId);
            if ($publication->exists) {
                $publication = ResultPublication::whereKey($publication->id)->lockForUpdate()->first();
            }
            $from = $publication->status;

            if ($action === 'lock' || $action === 'unlock') {
                $this->toggleLock($publication, $action === 'lock', $note, $userId);
            } else {
                [$allowed, $to, $needsNote] = self::TRANSITIONS[$action];
                if (! in_array($from, $allowed, true)) {
                    throw ValidationException::withMessages([
                        'action' => ["Cannot {$action} a result that is {$from}."],
                    ]);
                }
                if ($needsNote && trim((string) $note) === '') {
                    throw ValidationException::withMessages(['note' => ['A reason is required to '.$action.'.']]);
                }

                $stamp = match ($action) {
                    'submit' => ['submitted_by' => $userId, 'submitted_at' => now()],
                    'approve' => ['approved_by' => $userId, 'approved_at' => now()],
                    'publish' => [
                        'published_by' => $userId, 'published_at' => now(),
                        // Publishing freezes the results.
                        'is_locked' => true, 'locked_by' => $userId, 'locked_at' => now(),
                    ],
                    'reject' => ['approved_by' => null, 'approved_at' => null],
                    default => [],
                };

                $publication->fill(['status' => $to] + $stamp)->save();
            }

            ResultPublicationLog::create([
                'institute_id' => $publication->institute_id,
                'branch_id' => $publication->branch_id,
                'result_publication_id' => $publication->id,
                'action' => $action,
                'from_status' => $from,
                'to_status' => $publication->status,
                'note' => $note,
                'user_id' => $userId,
            ]);

            $this->lockCache = [];

            return $publication->fresh();
        });
    }

    private function toggleLock(ResultPublication $publication, bool $lock, ?string $note, ?int $userId): void
    {
        if (! $lock) {
            if ($publication->isPublished()) {
                throw ValidationException::withMessages(['action' => ['Unpublish the result before unlocking it.']]);
            }
            if (trim((string) $note) === '') {
                throw ValidationException::withMessages(['note' => ['A reason is required to unlock results.']]);
            }
        }

        $publication->fill([
            'is_locked' => $lock,
            'locked_by' => $lock ? $userId : null,
            'locked_at' => $lock ? now() : null,
        ])->save();
    }

    public function isPublished(int $examId, int $classId): bool
    {
        return ResultPublication::where('exam_id', $examId)
            ->where('class_id', $classId)
            ->where('status', ResultPublication::STATUS_PUBLISHED)
            ->exists();
    }

    /**
     * Refuse changes to a locked exam + class. Queries by explicit tenant so it
     * also holds for writes made outside a request (imports, CBT transfer).
     *
     * @throws ValidationException
     */
    public function assertUnlocked(int $examId, int $classId, ?int $instituteId = null, ?int $branchId = null): void
    {
        $instituteId ??= (int) get_institute_id();
        $branchId ??= (int) get_branch_id();
        $key = "{$instituteId}|{$branchId}|{$examId}|{$classId}";

        $locked = $this->lockCache[$key] ??= ResultPublication::withoutGlobalScopes()
            ->where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->where('exam_id', $examId)
            ->where('class_id', $classId)
            ->where('is_locked', true)
            ->exists();

        if ($locked) {
            throw ValidationException::withMessages([
                'exam_id' => [_lang('Results for this exam and class are locked. Ask an authorised user to unlock them first.')],
            ]);
        }
    }
}
