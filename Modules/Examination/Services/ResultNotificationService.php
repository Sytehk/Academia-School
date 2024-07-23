<?php

namespace Modules\Examination\Services;

use App\Services\AcademicContextService;
use App\Services\NotificationDispatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Exam;
use Modules\Academic\Models\Student;
use Modules\Examination\Models\GrandFinalSetting;

class ResultNotificationService
{
    public function __construct(
        private readonly TabulationSheetService $tabulationSheetService,
        private readonly NotificationDispatchService $dispatcher,
        private readonly GrandFinalResultService $grandFinalResultService,
        private readonly AcademicContextService $academicContext,
    ) {}

    public function queue(Request $request): array
    {
        $channel = $this->dispatcher->resolveChannel($request->input('notify_channel'), $request->input('sms_status'));

        if ($channel === NotificationDispatchService::CHANNEL_NONE) {
            return ['queued' => 0, 'skipped' => 0, 'channel' => $channel];
        }

        $examId = (int) $request->exam_id;
        $exam = Exam::find($examId);
        $examName = $exam?->name ?? 'Exam';
        $schoolName = (string) get_option('school_name');
        $contact = (string) get_option('exam_result_phone') ?: (string) get_option('phone');
        $senderId = (int) (Auth::id() ?: 0);

        $queued = 0;
        $skipped = 0;

        foreach ($this->resolveTargets($request) as $target) {
            $sheet = $this->tabulationSheetService->build(
                (int) $target['class_id'],
                $examId,
                $target['section_id'],
            );

            foreach ($sheet['rows'] ?? [] as $row) {
                if (($row['status'] ?? '') === 'blank') {
                    $skipped++;

                    continue;
                }

                $session = $row['student_session'] ?? null;
                $student = $session?->student instanceof Student
                    ? $session->student
                    : Student::find($row['student_id'] ?? 0);

                $phone = $this->dispatcher->studentPhone($student);
                if (! $phone) {
                    $skipped++;

                    continue;
                }

                $parameters = [
                    'student_name' => $row['student_name'] ?? trim(($student->first_name ?? '').' '.($student->last_name ?? '')),
                    'student_id' => $student?->id,
                    'roll' => $row['roll'] ?? '',
                    'class' => $session?->class?->class_name ?? ($sheet['class_name'] ?? ''),
                    'section' => $session?->section?->section_name ?? '',
                    'exam_name' => $examName,
                    'total_marks' => number_format((float) ($row['total_marks'] ?? 0), 2),
                    'gpa' => number_format((float) ($row['gpa'] ?? 0), 2),
                    'grade' => $row['grade'] ?? '',
                    'status' => strtoupper((string) ($row['status'] ?? '')),
                    'position' => $row['position'] ?? '-',
                    'school_name' => $schoolName,
                    'contact' => $contact,
                ];

                $message = $this->buildSmsMessage($parameters);

                if ($this->dispatcher->shouldSendSms($channel)) {
                    $this->dispatcher->queueSms(
                        $phone,
                        $message,
                        'Student',
                        $senderId,
                        [
                            'student_id' => $student?->id,
                            'user_type' => 'Student',
                        ]
                    );
                }

                if ($this->dispatcher->shouldSendWhatsApp($channel)) {
                    $this->dispatcher->queueWhatsApp(
                        $phone,
                        'result',
                        $parameters,
                        $student?->id,
                        'exam_result_'.$examId,
                        $student?->id
                    );
                }

                $queued++;
            }
        }

        return ['queued' => $queued, 'skipped' => $skipped, 'channel' => $channel];
    }

    /**
     * Grand Final has no row in the exams table — it is a computed combination of a
     * class's other exams (GrandFinalResultService) — so it needs its own dispatch path
     * rather than TabulationSheetService's per-exam query (FEAT-009).
     */
    public function queueGrandFinal(Request $request): array
    {
        $channel = $this->dispatcher->resolveChannel($request->input('notify_channel'), $request->input('sms_status'));

        if ($channel === NotificationDispatchService::CHANNEL_NONE) {
            return ['queued' => 0, 'skipped' => 0, 'channel' => $channel];
        }

        $schoolName = (string) get_option('school_name');
        $contact = (string) get_option('exam_result_phone') ?: (string) get_option('phone');
        $senderId = (int) (Auth::id() ?: 0);

        $queued = 0;
        $skipped = 0;

        foreach ($this->resolveTargets($request) as $target) {
            $studentSessions = $this->academicContext
                ->studentSessionQuery((int) $target['class_id'], $target['section_id'])
                ->with('student', 'session', 'class', 'section', 'group')
                ->get();

            $results = $this->grandFinalResultService->calculateForStudents($studentSessions);

            foreach ($results as $result) {
                if (! ($result['configured'] ?? false)) {
                    $skipped++;

                    continue;
                }

                $session = $result['student_session'];
                $student = $session->student;

                $phone = $this->dispatcher->studentPhone($student);
                if (! $phone) {
                    $skipped++;

                    continue;
                }

                $finalMark = $result['calculation_method'] === GrandFinalSetting::METHOD_FULL_MARKS
                    ? $result['final_obtained']
                    : $result['final_score'];

                $parameters = [
                    'student_name' => trim(($student->first_name ?? '').' '.($student->last_name ?? '')),
                    'roll' => $session->roll ?? '',
                    'class' => $session->class?->class_name ?? '',
                    'section' => $session->section?->section_name ?? '',
                    'total_marks' => number_format((float) ($finalMark ?? 0), 2),
                    'grade' => $result['grade_name'] ?? '',
                    'position' => $result['position'] ?? '-',
                    'school_name' => $schoolName,
                    'contact' => $contact,
                ];

                $message = $this->buildGrandFinalSmsMessage($parameters);

                if ($this->dispatcher->shouldSendSms($channel)) {
                    $this->dispatcher->queueSms(
                        $phone,
                        $message,
                        'Student',
                        $senderId,
                        [
                            'student_id' => $student?->id,
                            'user_type' => 'Student',
                        ]
                    );
                }

                if ($this->dispatcher->shouldSendWhatsApp($channel)) {
                    $this->dispatcher->queueWhatsApp(
                        $phone,
                        'grand_final_result',
                        $parameters,
                        $student?->id,
                        'grand_final_result_'.$target['class_id'],
                        $student?->id
                    );
                }

                $queued++;
            }
        }

        return ['queued' => $queued, 'skipped' => $skipped, 'channel' => $channel];
    }

    private function buildGrandFinalSmsMessage(array $parameters): string
    {
        return "Dear Guardian, Grand Final result of {$parameters['student_name']} (Roll: {$parameters['roll']}) "
            ."— Class: {$parameters['class']}"
            .($parameters['section'] ? " Section: {$parameters['section']}" : '')
            .". Total: {$parameters['total_marks']}, Grade: {$parameters['grade']}, "
            ."Position: {$parameters['position']}. {$parameters['school_name']}"
            .($parameters['contact'] ? " Contact: {$parameters['contact']}" : '');
    }

    private function resolveTargets(Request $request): array
    {
        $scope = (string) $request->input('scope', 'current');
        $sectionId = $request->filled('section_id') ? (int) $request->section_id : null;

        if ($scope === 'institute') {
            return ClassModel::query()
                ->where('institute_id', get_institute_id())
                ->pluck('id')
                ->map(fn ($classId) => ['class_id' => (int) $classId, 'section_id' => null])
                ->all();
        }

        if ($scope === 'classes') {
            $classIds = collect((array) $request->input('class_ids', []))
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            return $classIds->map(fn ($classId) => ['class_id' => $classId, 'section_id' => null])->all();
        }

        if ($scope === 'class') {
            return [[
                'class_id' => (int) $request->class_id,
                'section_id' => null,
            ]];
        }

        return [[
            'class_id' => (int) $request->class_id,
            'section_id' => $sectionId,
        ]];
    }

    private function buildSmsMessage(array $parameters): string
    {
        return "Dear Guardian, result of {$parameters['student_name']} (Roll: {$parameters['roll']}) "
            ."for {$parameters['exam_name']} — Class: {$parameters['class']}"
            .($parameters['section'] ? " Section: {$parameters['section']}" : '')
            .". Total: {$parameters['total_marks']}, GPA: {$parameters['gpa']}, Grade: {$parameters['grade']}, "
            ."Status: {$parameters['status']}. {$parameters['school_name']}"
            .($parameters['contact'] ? " Contact: {$parameters['contact']}" : '');
    }
}
