<?php

namespace Modules\Examination\Services;

use Modules\Academic\Models\StudentSession;
use Modules\Academic\Models\Subject;

class EmptyMarkSheetService
{
    public function __construct(
        private readonly ResultCalculationService $resultCalculationService,
        private readonly ResultCardService $resultCardService,
        private readonly ExamContextService $examContextService,
    ) {}

    /**
     * Build a completely empty printable mark input sheet. Existing marks are never included.
     */
    public function build(int $classId, int $examId, ?int $sectionId = null, ?int $groupId = null, array $subjectIds = [], ?int $studentLimit = null): array
    {
        $query = StudentSession::with(['student', 'class', 'section', 'group', 'session'])
            ->where('class_id', $classId);

        $this->examContextService->applySessionFilters($query, $sectionId, $groupId);

        $students = $query->orderByRaw('CAST(roll AS UNSIGNED), roll')->get();
        if ($studentLimit) {
            $students = $students->take($studentLimit);
        }

        $subjects = $this->resultCalculationService->subjectsForClassGroup($classId, $groupId ?? $students->first()?->group_id);
        if ($subjectIds) {
            $subjects = $subjects->whereIn('id', $subjectIds)->values();
        }

        $first = $students->first();

        return [
            'header' => $this->resultCardService->schoolHeader(),
            'class_name' => $first?->class?->class_name,
            'section_name' => $first?->section?->section_name,
            'group_name' => $first?->group?->group_name,
            'session_name' => $first?->session?->session,
            'exam_id' => $examId,
            'student_count' => $students->count(),
            'subjects' => $subjects->map(fn (Subject $subject) => [
                'id' => $subject->id,
                'name' => $subject->subject_name,
                'code' => $subject->subject_code,
            ])->values(),
            'students' => $students->values()->map(fn (StudentSession $session, int $index) => [
                'sl' => str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                'student_name' => trim(($session->student->first_name ?? '').' '.($session->student->last_name ?? '')),
                'roll' => $session->roll,
                'registration_no' => $session->student?->registration_no,
            ]),
        ];
    }
}
