<?php

namespace Modules\Examination\Services;

use Illuminate\Support\Collection;
use Modules\Academic\Models\Exam;
use Modules\Academic\Models\StudentSession;
use Modules\Examination\Models\AssessmentDomain;
use Modules\Examination\Models\ExamMark;
use Modules\Examination\Models\GrandFinalClassExam;
use Modules\Examination\Models\GrandFinalSetting;
use Modules\Examination\Models\ResultCardSetting;
use Modules\Examination\Models\StudentAssessment;

class ResultCardService
{
    public function __construct(
        private readonly ResultCalculationService $resultCalculationService,
        private readonly GrandFinalResultService $grandFinalResultService,
        private readonly TabulationSheetService $tabulationSheetService,
        private readonly ExamContextService $examContextService,
        private readonly ReportCardDataService $reportData,
    ) {}

    public function schoolHeader(): array
    {
        $branding = $this->examContextService->instituteBranding();
        $settings = $this->settings()['settings'];

        return [
            'school_name' => get_option('school_name'),
            'address' => get_option('address'),
            'phone' => get_option('phone'),
            'email' => get_option('email'),
            'website' => get_option('website_link'),
            'eiin_code' => get_option('eiin_code'),
            'institute_code' => get_option('institute_code'),
            'logo' => $branding['header_logo'] ?: get_logo(),
            'banner_image' => $branding['banner_image'],
            'signatures' => $this->examContextService->resolveSignatures($settings),
            'theme' => [
                'primary_color' => $settings['primary_color'] ?? '#1a365d',
                'accent_color' => $settings['accent_color'] ?? '#c9a227',
                'watermark_opacity' => (float) ($settings['watermark_opacity'] ?? 0.08),
                'show_watermark' => (bool) ($settings['show_watermark'] ?? true),
            ],
        ];
    }

    public function settings(): array
    {
        $row = ResultCardSetting::query()->first();

        return [
            'result_title' => $row?->result_title,
            'settings' => $row ? $row->mergedSettings() : ResultCardSetting::defaults(),
        ];
    }

    /**
     * One student's term result card.
     *
     * @param  Collection|null  $classRows  ranked class results (ResultCalculationService::buildClassTermResults)
     *                                      — pass them when printing a whole class so they are computed once.
     */
    public function singleTermCard(StudentSession $studentSession, int $examId, ?int $position = null, ?Collection $classRows = null): array
    {
        $classRows ??= $this->resultCalculationService->buildClassTermResults(
            (int) $studentSession->class_id,
            $examId,
            $studentSession->section_id ? (int) $studentSession->section_id : null,
            $studentSession->group_id ? (int) $studentSession->group_id : null,
            null,
            (int) $studentSession->session_id,
        );

        $result = $classRows->firstWhere('student_id', $studentSession->student_id)
            ?? $this->resultCalculationService->buildStudentTermResult($studentSession, $examId);
        if ($position !== null) {
            $result['position'] = $position;
        }

        $data = $this->reportData;
        $stats = $data->classStatistics($classRows);
        $myPositions = $stats['positions'][$studentSession->student_id] ?? [];
        $result['subjects'] = array_map(function (array $row) use ($stats, $myPositions) {
            $subjectStats = $stats['subjects'][$row['subject_id']] ?? null;

            return $row + [
                'subject_position' => $row['obtained'] !== null ? ($myPositions[$row['subject_id']] ?? null) : null,
                'class_highest' => $subjectStats['highest'] ?? null,
                'class_lowest' => $subjectStats['lowest'] ?? null,
                'class_average' => $subjectStats['average'] ?? null,
            ];
        }, $result['subjects']);
        $result['class_statistics'] = $stats['class'];
        $result['students_in_class'] = $stats['class']['students_in_class'];
        $result['average'] = $result['percentage'];

        $term = $data->termDetails($examId);
        $result['term'] = $term ? [
            'term_number' => $term->term_number,
            'term_start_date' => $term->term_start_date?->toDateString(),
            'term_end_date' => $term->term_end_date?->toDateString(),
            'next_term_begins' => $term->next_term_begins?->toDateString(),
            'next_term_fee' => $term->next_term_fee !== null ? (float) $term->next_term_fee : null,
            'next_term_note' => $term->next_term_note,
        ] : null;
        $result['term_attendance'] = $data->termAttendance($studentSession, $term);
        $result['cumulative'] = $data->cumulative($studentSession, $examId, $result['subjects']);

        $comments = $data->comments((int) $studentSession->student_id, $examId, $result['grade'] ?? null);
        $result['teacher_remarks'] = $comments['teacher_comment'];
        $result['comments'] = $comments;

        $result['domains'] = $this->domainAssessments(
            (int) $studentSession->student_id,
            $examId,
            (int) $studentSession->class_id
        );

        $config = $this->settings();
        $exam = Exam::find($examId);
        $header = $this->schoolHeader();

        return [
            'header' => $header,
            'settings' => $config['settings'],
            'card_settings' => $config['settings'],
            'result_title' => $config['result_title'] ?: 'Academic Result Card',
            'exam_name' => $exam?->name,
            'exam_id' => $examId,
            'result' => $result,
            'domains' => $result['domains'],
        ];
    }

    public function annualCard(StudentSession $studentSession): array
    {
        $grand = $this->grandFinalResultService->calculateForStudentSession($studentSession);
        $terms = GrandFinalClassExam::where('class_id', $studentSession->class_id)
            ->orderBy('serial_no')
            ->with('exam:id,name')
            ->get();

        $subjects = $this->resultCalculationService->subjectsForClassGroup(
            (int) $studentSession->class_id,
            $studentSession->group_id
        );

        $examIds = $terms->pluck('exam_id')->all();
        $marks = ExamMark::where('student_id', $studentSession->student_id)
            ->whereIn('exam_id', $examIds ?: [0])
            ->get()
            ->groupBy('subject_id');

        $subjectComparison = [];
        foreach ($subjects as $subject) {
            $termMarks = [];
            $values = [];
            foreach ($terms as $term) {
                $mark = $marks->get($subject->id, collect())->firstWhere('exam_id', $term->exam_id);
                $obtained = $mark ? (float) $mark->total_marks : null;
                $termMarks[] = [
                    'exam_id' => $term->exam_id,
                    'exam_name' => $term->exam?->name,
                    'obtained' => $obtained,
                ];
                if ($obtained !== null) {
                    $values[] = $obtained;
                }
            }

            $average = count($values) ? round(array_sum($values) / count($values), 2) : null;
            $subjectComparison[] = [
                'subject_id' => $subject->id,
                'subject_name' => $subject->subject_name,
                'terms' => $termMarks,
                'average' => $average,
            ];
        }

        $classmates = StudentSession::where('class_id', $studentSession->class_id)
            ->when($studentSession->section_id, fn ($query) => $query->where('section_id', $studentSession->section_id))
            ->with('student', 'session', 'class', 'section', 'group')
            ->orderByRaw('CAST(roll AS UNSIGNED), roll')
            ->get();
        $ranked = $this->grandFinalResultService->calculateForStudents($classmates);
        $self = $ranked->first(fn (array $row) => $row['student_session']->student_id === $studentSession->student_id);

        $config = $this->settings();
        $header = $this->schoolHeader();

        return [
            'header' => $header,
            'settings' => $config['settings'],
            'card_settings' => $config['settings'],
            'result_title' => $config['result_title'] ?: 'Annual / All Term Result Card',
            'terms' => $terms,
            'calculation_method' => $grand['calculation_method'] ?? GrandFinalSetting::METHOD_WEIGHTED_PERCENTAGE,
            'grand' => $self ?? $grand,
            'subject_comparison' => $subjectComparison,
            'domains' => $this->domainAssessments(
                (int) $studentSession->student_id,
                (int) ($terms->last()?->exam_id ?? 0),
                (int) $studentSession->class_id
            ),
            'student_session' => $studentSession,
        ];
    }

    public function domainAssessments(int $studentId, int $examId, int $classId): array
    {
        $domains = AssessmentDomain::with(['items' => fn ($query) => $query->where('is_active', true)])
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        $scores = StudentAssessment::where('student_id', $studentId)
            ->where('exam_id', $examId)
            ->get()
            ->keyBy('assessment_item_id');

        return $domains->map(function (AssessmentDomain $domain) use ($scores) {
            return [
                'id' => $domain->id,
                'type' => $domain->domain_type,
                'name' => $domain->name,
                'items' => $domain->items->map(function ($item) use ($scores) {
                    $score = $scores->get($item->id);

                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'max_score' => (float) $item->max_score,
                        'rating_type' => $item->rating_type,
                        'grade_scale' => $item->grade_scale,
                        'score' => $score?->score,
                        'rating' => $score?->rating,
                        'remarks' => $score?->remarks,
                    ];
                })->values(),
            ];
        })->all();
    }
}
