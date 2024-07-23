<?php

namespace Modules\Examination\Services;

use App\Services\AcademicContextService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Examination\Models\AssessmentItem;
use Modules\Examination\Models\StudentAssessment;

/**
 * Saves affective / psychomotor ratings for a class, shared by the API and
 * the web entry screen: students must be enrolled in the class/section, items
 * must belong to the institute, scores may not exceed the item maximum, and a
 * locked result cannot be changed.
 */
class StudentAssessmentService
{
    public function __construct(
        private readonly AcademicContextService $academicContext,
        private readonly ResultPublicationService $publications,
    ) {}

    /**
     * @param  array<int|string, array<int|string, array{score?: mixed, rating?: ?string, remarks?: ?string}>>  $entries  student_id => item_id => payload
     *
     * @throws ValidationException
     */
    public function save(int $classId, ?int $sectionId, int $examId, array $entries): int
    {
        $this->academicContext->assertClassSectionGroup($classId, $sectionId);
        $this->publications->assertUnlocked($examId, $classId);
        $this->academicContext->assertStudentsInClassSection(array_keys($entries), $classId, $sectionId);

        $itemIds = collect($entries)->flatMap(fn ($items) => array_keys((array) $items))->map(fn ($id) => (int) $id)->unique();
        $items = AssessmentItem::whereIn('id', $itemIds)->get()->keyBy('id');
        if ($items->count() !== $itemIds->count()) {
            throw ValidationException::withMessages(['entries' => [_lang('One or more assessment items are invalid.')]]);
        }

        $sessionId = $this->academicContext->currentSessionId();
        $saved = 0;

        DB::transaction(function () use ($entries, $items, $classId, $sectionId, $examId, $sessionId, &$saved) {
            foreach ($entries as $studentId => $studentItems) {
                foreach ((array) $studentItems as $itemId => $payload) {
                    $max = (float) $items->get((int) $itemId)->max_score;
                    $score = $payload['score'] ?? null;
                    if ($score !== null && $score !== '' && (! is_numeric($score) || $score < 0 || ($max > 0 && (float) $score > $max))) {
                        throw ValidationException::withMessages(['entries' => [_lang('A score is outside the range of its assessment item.')]]);
                    }

                    // Keyed exactly like the student_assessments_unique index.
                    StudentAssessment::updateOrCreate(
                        [
                            'institute_id' => get_institute_id(),
                            'session_id' => $sessionId,
                            'student_id' => (int) $studentId,
                            'exam_id' => $examId,
                            'assessment_item_id' => (int) $itemId,
                        ],
                        [
                            'branch_id' => get_branch_id(),
                            'class_id' => $classId,
                            'section_id' => $sectionId,
                            'score' => $score === '' ? null : $score,
                            'rating' => $payload['rating'] ?? null,
                            'remarks' => $payload['remarks'] ?? null,
                        ]
                    );
                    $saved++;
                }
            }
        });

        return $saved;
    }
}
