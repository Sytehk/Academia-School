<?php

namespace Modules\Ai\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Services\ClassService;
use Modules\Ai\Services\ClaudeService;

/**
 * Summarizes existing report data into a plain-language answer. This controller never
 * re-derives report logic — it builds a compact snapshot from the same tables the
 * existing attendance/exam report screens already query, then hands that snapshot to
 * ClaudeService::summarize() for the write-up.
 */
class AiInsightsController extends Controller
{
    public const REPORT_TYPES = [
        'attendance_trend' => 'Attendance Trend',
        'exam_performance' => 'Exam Performance',
    ];

    public function __construct(
        private readonly ClaudeService $claudeService,
        private readonly ClassService $classService
    ) {}

    public function index(): Factory|View
    {
        return view('ai::insights.index', [
            'reportTypes' => self::REPORT_TYPES,
            'classes' => $this->classService->getClasses(),
            'apiKeyConfigured' => ClaudeService::apiKeyConfigured(),
        ]);
    }

    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'report_type' => 'required|string|in:'.implode(',', array_keys(self::REPORT_TYPES)),
            'class_id' => 'nullable|integer',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'question' => 'nullable|string|max:1000',
        ]);

        if (! ClaudeService::apiKeyConfigured()) {
            return response()->json([
                'status' => false,
                'message' => 'The Anthropic API key has not been configured yet. Ask an administrator to set it under AI Assistant > Settings.',
            ], 422);
        }

        try {
            $fromDate = $request->from_date ?? Carbon::now()->startOfMonth()->format('Y-m-d');
            $toDate = $request->to_date ?? Carbon::now()->format('Y-m-d');
            $classId = (int) $request->class_id;

            $snapshot = $request->report_type === 'attendance_trend'
                ? $this->attendanceSnapshot($classId, $fromDate, $toDate)
                : $this->examPerformanceSnapshot($classId, $fromDate, $toDate);

            $question = $request->question ?: 'Summarize the key trends and any notable outliers in this data for a school administrator.';

            $summary = $this->claudeService->summarize($snapshot, $question);

            return response()->json([
                'status' => true,
                'summary' => $summary,
                'raw_snapshot' => $snapshot,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function attendanceSnapshot(int $classId, string $fromDate, string $toDate): string
    {
        $query = DB::table('student_attendances')
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->whereBetween('date', [$fromDate, $toDate]);

        if ($classId > 0) {
            $query->where('class_id', $classId);
        }

        $totals = (clone $query)
            ->selectRaw('attendance, count(*) as total')
            ->groupBy('attendance')
            ->pluck('total', 'attendance');

        $byDate = (clone $query)
            ->selectRaw('date, attendance, count(*) as total')
            ->groupBy('date', 'attendance')
            ->orderBy('date')
            ->get()
            ->groupBy('date')
            ->map(fn ($rows) => $rows->pluck('total', 'attendance'));

        $lines = [];
        $lines[] = "Attendance report for {$fromDate} to {$toDate}".($classId > 0 ? ' (class filter applied)' : ' (all classes)').':';
        $lines[] = 'Overall totals — Present: '.($totals[1] ?? 0).', Absent: '.($totals[2] ?? 0).', Late: '.($totals[3] ?? 0);
        $lines[] = 'Daily breakdown (date: present/absent/late):';
        foreach ($byDate as $date => $counts) {
            $lines[] = "  {$date}: ".($counts[1] ?? 0).'/'.($counts[2] ?? 0).'/'.($counts[3] ?? 0);
        }

        return implode("\n", $lines);
    }

    private function examPerformanceSnapshot(int $classId, string $fromDate, string $toDate): string
    {
        $query = DB::table('exam_marks')
            ->join('exams', 'exams.id', '=', 'exam_marks.exam_id')
            ->where('exam_marks.institute_id', get_institute_id())
            ->where('exam_marks.branch_id', get_branch_id())
            ->whereBetween('exams.created_at', [$fromDate, $toDate.' 23:59:59']);

        if ($classId > 0) {
            $query->where('exam_marks.class_id', $classId);
        }

        $bySubject = (clone $query)
            ->join('subjects', 'subjects.id', '=', 'exam_marks.subject_id')
            ->selectRaw('subjects.subject_name, avg(exam_marks.total_marks) as avg_marks, count(*) as entries')
            ->groupBy('subjects.subject_name')
            ->get();

        $byGrade = (clone $query)
            ->selectRaw('exam_marks.grade, count(*) as total')
            ->groupBy('exam_marks.grade')
            ->pluck('total', 'grade');

        $lines = [];
        $lines[] = "Exam performance report for exams created between {$fromDate} and {$toDate}".($classId > 0 ? ' (class filter applied)' : ' (all classes)').':';
        $lines[] = 'Average total marks per subject:';
        foreach ($bySubject as $row) {
            $lines[] = '  '.$row->subject_name.': '.round((float) $row->avg_marks, 1).' avg ('.$row->entries.' entries)';
        }
        $lines[] = 'Grade distribution:';
        foreach ($byGrade as $grade => $count) {
            $lines[] = "  {$grade}: {$count}";
        }

        return implode("\n", $lines);
    }
}
