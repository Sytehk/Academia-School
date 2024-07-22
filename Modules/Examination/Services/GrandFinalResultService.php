<?php

namespace Modules\Examination\Services;

use Illuminate\Support\Collection;
use Modules\Academic\Models\StudentSession;
use Modules\Examination\Models\ExamMark;
use Modules\Examination\Models\GrandFinalClassExam;
use Modules\Examination\Models\GrandFinalSetting;

class GrandFinalResultService
{
    public function __construct(
        private readonly GradeCalculationService $gradeCalculationService,
        private readonly MarkEntryService $markEntryService,
    ) {}

    /**
     * Calculate the Grand Final Result for a single student session.
     */
    public function calculateForStudentSession(StudentSession $studentSession): array
    {
        $classId = (int) $studentSession->class_id;

        $grandFinalExams = GrandFinalClassExam::where('class_id', $classId)
            ->orderBy('serial_no')
            ->with('exam:id,name')
            ->get();

        if ($grandFinalExams->isEmpty()) {
            return [
                'configured' => false,
                'student_session' => $studentSession,
            ];
        }

        $calculationMethod = GrandFinalSetting::where('class_id', $classId)->value('calculation_method')
            ?? GrandFinalSetting::METHOD_WEIGHTED_PERCENTAGE;

        $breakdown = [];
        $weightedScore = 0.0;
        $totalObtained = 0.0;
        $totalFullMarks = 0.0;

        foreach ($grandFinalExams as $grandFinalExam) {
            $examMarks = ExamMark::where('student_id', $studentSession->student_id)
                ->where('exam_id', $grandFinalExam->exam_id)
                ->with('subject:id,subject_name')
                ->get();

            $obtained = (float) $examMarks->sum('total_marks');
            $fullMarks = (float) $examMarks->sum(
                fn (ExamMark $mark) => $this->markEntryService->resolveMaxMarks((int) $mark->subject_id, (int) $grandFinalExam->exam_id)
            );

            $examPercentage = $this->gradeCalculationService->calculatePercentage($obtained, $fullMarks);

            $breakdown[] = [
                'exam_id' => $grandFinalExam->exam_id,
                'exam_name' => $grandFinalExam->exam->name ?? 'Unknown Exam',
                'serial_no' => $grandFinalExam->serial_no,
                'weight' => (float) $grandFinalExam->percentage,
                'obtained' => $obtained,
                'full_marks' => $fullMarks,
                'percentage' => $examPercentage,
            ];

            $weightedScore += $examPercentage * ((float) $grandFinalExam->percentage / 100);
            $totalObtained += $obtained;
            $totalFullMarks += $fullMarks;
        }

        if ($calculationMethod === GrandFinalSetting::METHOD_FULL_MARKS) {
            $gradeBasis = $this->gradeCalculationService->calculatePercentage($totalObtained, $totalFullMarks);
            $finalObtained = $totalObtained;
            $finalFullMarks = $totalFullMarks;
            $finalScore = null;
        } else {
            $gradeBasis = round($weightedScore, 2);
            $finalObtained = null;
            $finalFullMarks = null;
            $finalScore = $gradeBasis;
        }

        $grade = $this->gradeCalculationService->resolveGrade($classId, $gradeBasis, $studentSession->institute_id ?? null);

        return [
            'configured' => true,
            'student_session' => $studentSession,
            'calculation_method' => $calculationMethod,
            'breakdown' => $breakdown,
            'final_score' => $finalScore,
            'final_obtained' => $finalObtained,
            'final_full_marks' => $finalFullMarks,
            'grade_name' => $grade->grade_name ?? 'F',
            'grade_point' => $grade->grade_point ?? 0,
        ];
    }

    /**
     * Calculate the Grand Final Result for a set of student sessions, ranking them
     * against each other (competition ranking, ties share a position).
     */
    public function calculateForStudents(Collection $studentSessions): Collection
    {
        $results = $studentSessions->map(fn (StudentSession $studentSession) => $this->calculateForStudentSession($studentSession));

        $ranked = $results->filter(fn (array $result) => $result['configured']);

        $sorted = $ranked->sortByDesc(
            fn (array $result) => $result['calculation_method'] === GrandFinalSetting::METHOD_FULL_MARKS
                ? $result['final_obtained']
                : $result['final_score']
        )->values();

        $position = 0;
        $previousValue = null;
        $rankByStudentId = [];

        foreach ($sorted as $index => $result) {
            $value = $result['calculation_method'] === GrandFinalSetting::METHOD_FULL_MARKS
                ? $result['final_obtained']
                : $result['final_score'];

            if ($value !== $previousValue) {
                $position = $index + 1;
                $previousValue = $value;
            }

            $rankByStudentId[$result['student_session']->student_id] = $position;
        }

        return $results->map(function (array $result) use ($rankByStudentId) {
            $result['position'] = $rankByStudentId[$result['student_session']->student_id] ?? null;

            return $result;
        });
    }
}
