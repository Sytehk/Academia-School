<?php

namespace Modules\Examination\Services;

use Illuminate\Support\Collection;
use Modules\Academic\Models\ExamAttendance;
use Modules\Academic\Models\StudentSession;
use Modules\Academic\Models\Subject;
use Modules\Examination\Models\ExamMark;
use Modules\Examination\Models\MarkConfig;
use Modules\Examination\Models\RemarkConfig;
use Modules\Examination\Models\ResultCardSetting;

class ResultCalculationService
{
    public function __construct(
        private readonly GradeCalculationService $gradeCalculationService,
        private readonly MarkEntryService $markEntryService,
        private readonly PositionCalculationService $positionCalculationService,
        private readonly ExamContextService $examContextService,
    ) {}

    /**
     * Build a complete single-term result for one student session.
     */
    public function buildStudentTermResult(StudentSession $studentSession, int $examId): array
    {
        $subjects = $this->subjectsForClassGroup((int) $studentSession->class_id, $studentSession->group_id);
        $marks = ExamMark::where('student_id', $studentSession->student_id)
            ->where('exam_id', $examId)
            ->where('class_id', $studentSession->class_id)
            ->with('subject:id,subject_name,subject_code,full_mark,pass_mark')
            ->get()
            ->keyBy('subject_id');

        $componentMap = $this->componentMapForExam((int) $studentSession->class_id, $examId, $studentSession->group_id);

        $subjectRows = [];
        $totalObtained = 0.0;
        $totalFullMarks = 0.0;
        $totalPoints = 0.0;
        $countedSubjects = 0;
        $failedSubjects = [];
        $hasFail = false;
        $missingSubjects = [];

        foreach ($subjects as $subject) {
            $mark = $marks->get($subject->id);
            $components = $componentMap->get($subject->id, collect());
            // Derived from $componentMap (already loaded, above, for every
            // subject in the class/exam/group in ONE query) instead of calling
            // back into MarkEntryService, which would run its own MarkConfig
            // query per subject -- ~4 queries per subject, all answerable from
            // data already sitting in memory two lines up.
            [$fullMarks, $passMarks] = $this->fullAndPassMarksFromComponents($components);

            if (! $mark) {
                $missingSubjects[] = $subject->subject_name;
                $subjectRows[] = [
                    'subject_id' => $subject->id,
                    'subject_name' => $subject->subject_name,
                    'subject_code' => $subject->subject_code,
                    'full_marks' => $fullMarks,
                    'pass_marks' => $passMarks,
                    'obtained' => null,
                    'percentage' => null,
                    'grade' => null,
                    'grade_point' => null,
                    'status' => 'missing',
                    'is_absent' => false,
                    'is_exempted' => false,
                    'components' => $this->emptyComponents($components),
                    'remarks' => null,
                ];

                continue;
            }

            $obtained = (float) $mark->total_marks;
            $percentage = (float) ($mark->percentage ?: $this->gradeCalculationService->calculatePercentage($obtained, $fullMarks));
            $status = $mark->status ?: (($obtained < $passMarks || $mark->grade === 'F') ? 'fail' : 'pass');

            if ($mark->is_absent) {
                $status = 'absent';
            } elseif ($mark->is_exempted) {
                $status = 'exempted';
            }

            if (in_array($status, ['fail', 'absent'], true)) {
                $hasFail = true;
                $failedSubjects[] = $subject->subject_name;
            }

            if (! $mark->is_exempted) {
                $totalObtained += $obtained;
                $totalFullMarks += $fullMarks;
                $totalPoints += (float) $mark->grade_point;
                $countedSubjects++;
            }

            $subjectRows[] = [
                'subject_id' => $subject->id,
                'subject_name' => $subject->subject_name,
                'subject_code' => $subject->subject_code,
                'full_marks' => $fullMarks,
                'pass_marks' => $passMarks,
                'obtained' => $obtained,
                'percentage' => $percentage,
                'grade' => $mark->grade,
                'grade_point' => (float) $mark->grade_point,
                'status' => $status,
                'is_absent' => (bool) $mark->is_absent,
                'is_exempted' => (bool) $mark->is_exempted,
                'components' => $this->componentValues($mark, $components),
                'remarks' => $mark->remarks,
            ];
        }

        $gpa = $countedSubjects > 0 ? round($totalPoints / $countedSubjects, 2) : 0.0;
        $overallPercentage = $this->gradeCalculationService->calculatePercentage($totalObtained, $totalFullMarks);
        $overallGrade = $this->resolveOverallGrade((int) $studentSession->class_id, $gpa, $overallPercentage, $studentSession->institute_id);
        $status = $countedSubjects === 0 ? 'not_published' : ($hasFail ? 'fail' : 'pass');
        $teacherRemarks = RemarkConfig::where('remark_title', $overallGrade['grade_name'] ?? '')->value('remarks');

        $presentCount = ExamAttendance::where('student_id', $studentSession->student_id)
            ->where('exam_id', $examId)
            ->where('attendance', 1)
            ->count();
        $attendanceTotal = ExamAttendance::where('student_id', $studentSession->student_id)
            ->where('exam_id', $examId)
            ->count();

        return [
            'student_id' => $studentSession->student_id,
            'student_session' => $studentSession,
            'student_name' => trim(($studentSession->student->first_name ?? '').' '.($studentSession->student->last_name ?? '')),
            'student_image' => $studentSession->student?->user?->image,
            'registration_no' => $studentSession->student?->registration_no,
            'roll' => $studentSession->roll,
            'birthday' => $studentSession->student?->birthday,
            'class_id' => $studentSession->class_id,
            'class_name' => $studentSession->class?->class_name,
            'section_name' => $studentSession->section?->section_name,
            'group_name' => $studentSession->group?->group_name,
            'session_name' => $studentSession->session?->session,
            'subjects' => $subjectRows,
            'total_marks' => $totalObtained,
            'total_full_marks' => $totalFullMarks,
            'percentage' => $overallPercentage,
            'gpa' => $gpa,
            'grade' => $overallGrade['grade_name'] ?? 'N/A',
            'grade_point' => $overallGrade['grade_point'] ?? $gpa,
            'status' => $status,
            'failed_subjects' => $failedSubjects,
            'failed_subject_count' => count($failedSubjects),
            'missing_subjects' => $missingSubjects,
            'teacher_remarks' => $teacherRemarks,
            'attendance_present' => $presentCount,
            'attendance_total' => $attendanceTotal,
            'position' => null,
        ];
    }

    /**
     * Build ranked single-term results for a class/section/exam.
     */
    public function buildClassTermResults(int $classId, int $examId, ?int $sectionId = null, ?int $groupId = null, ?string $search = null, ?int $sessionId = null): Collection
    {
        $query = StudentSession::with(['student.user', 'session', 'class', 'section', 'group'])
            ->where('class_id', $classId);

        if ($sessionId) {
            // An explicit (possibly previous) academic session instead of the active one.
            $query->where('session_id', $sessionId);
        }
        $this->examContextService->applySessionFilters($query, $sectionId, $groupId, (bool) $sessionId);
        if ($search) {
            $query->where(function ($inner) use ($search) {
                $inner->where('roll', 'like', '%'.$search.'%')
                    ->orWhereHas('student', function ($studentQuery) use ($search) {
                        $studentQuery->where('first_name', 'like', '%'.$search.'%')
                            ->orWhere('last_name', 'like', '%'.$search.'%')
                            ->orWhere('registration_no', 'like', '%'.$search.'%');
                    });
            });
        }

        $sessions = $query->orderByRaw('CAST(roll AS UNSIGNED), roll')->get();
        $results = $sessions->map(fn (StudentSession $session) => $this->buildStudentTermResult($session, $examId));

        $settings = $this->resultCardSettings();
        $failedPolicy = $settings['rank_failed_students'] ?? PositionCalculationService::FAILED_WITH_OTHERS;
        $method = $this->positionCalculationService->resolveMethodForClassExam($classId, $examId);

        return $this->positionCalculationService->rank($results, $method, $failedPolicy);
    }

    public function subjectsForClassGroup(int $classId, ?int $groupId): Collection
    {
        return Subject::where('class_id', $classId)
            ->where(function ($query) use ($groupId) {
                $query->whereNull('group_id');
                if ($groupId) {
                    $query->orWhere('group_id', $groupId);
                }
            })
            ->orderBy('id')
            ->get(['id', 'subject_name', 'subject_code', 'full_mark', 'pass_mark', 'group_id', 'class_id']);
    }

    public function componentMapForExam(int $classId, int $examId, ?int $groupId): Collection
    {
        $query = MarkConfig::with('mark_config_exam_code')
            ->where('class_id', $classId)
            ->where('exam_id', $examId);

        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        return $query->get()->groupBy('subject_id');
    }

    public function resultCardSettings(): array
    {
        $row = ResultCardSetting::query()->first();

        return $row ? $row->mergedSettings() : ResultCardSetting::defaults();
    }

    private function resolveOverallGrade(int $classId, float $gpa, float $percentage, ?int $instituteId): array
    {
        // Shares GradeCalculationService's memoised scale instead of issuing its
        // own query, so this costs nothing once the scale has been loaded.
        $byPoint = $this->gradeCalculationService->resolveGradeByPoint($gpa, $instituteId);

        if ($byPoint) {
            return [
                'grade_name' => $byPoint->grade_name,
                'grade_point' => (float) $byPoint->grade_point,
            ];
        }

        $byPercent = $this->gradeCalculationService->resolveGrade($classId, $percentage, $instituteId);

        return [
            'grade_name' => $byPercent->grade_name ?? 'F',
            'grade_point' => (float) ($byPercent->grade_point ?? 0),
        ];
    }

    /**
     * Sum a subject's configured full/pass marks from an already-loaded
     * MarkConfig component collection, mirroring
     * MarkEntryService::resolveMaxMarks()/resolvePassMarks() exactly (including
     * their 100 / 33 fallbacks) but without either method's own query --
     * $components here comes from componentMapForExam()'s single class-wide
     * fetch, not a fresh per-subject lookup.
     *
     * @return array{0: float, 1: float} [fullMarks, passMarks]
     */
    private function fullAndPassMarksFromComponents(Collection $components): array
    {
        $configuredTotal = $components->pluck('mark_config_exam_code.total_marks')->filter()->sum();
        $configuredPass = $components->pluck('mark_config_exam_code.pass_mark')->filter(fn ($value) => $value !== null)->sum();

        return [
            $configuredTotal > 0 ? (float) $configuredTotal : 100.0,
            $configuredPass > 0 ? (float) $configuredPass : 33.0,
        ];
    }

    private function componentValues(ExamMark $mark, Collection $components): array
    {
        $values = [];
        $slot = 1;
        foreach ($components as $component) {
            $code = $component->mark_config_exam_code;
            $values[] = [
                'title' => $code->title ?? ('Mark '.$slot),
                'full_marks' => (float) ($code->total_marks ?? 0),
                'pass_marks' => (float) ($code->pass_mark ?? 0),
                'obtained' => (float) $mark->{"mark{$slot}"},
            ];
            $slot++;
        }

        return $values;
    }

    private function emptyComponents(Collection $components): array
    {
        $values = [];
        foreach ($components as $component) {
            $code = $component->mark_config_exam_code;
            $values[] = [
                'title' => $code->title ?? 'Mark',
                'full_marks' => (float) ($code->total_marks ?? 0),
                'pass_marks' => (float) ($code->pass_mark ?? 0),
                'obtained' => null,
            ];
        }

        return $values;
    }
}
