<?php

namespace Modules\Authentication\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\ZoomMeeting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Academic\Models\ClassRoutine;
use Modules\Academic\Models\Notice;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\StudentAttendance;
use Modules\Accounting\Models\AccountTransactionDetail;
use Modules\Authentication\Models\User;
use Modules\Library\Models\Book;
use Modules\Library\Models\BookIssue;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userType = auth()->user()->user_type;

        return match ($userType) {
            'Admin' => $this->adminDashboard($request),
            'Teacher' => $this->teacherDashboard(),
            'Student' => $this->studentDashboard(),
            // Parents get their own panel — previously they fell through to the
            // admin dashboard below, exposing institute-wide data.
            'Parent' => redirect()->route('parent.dashboard'),

            // Other back-office staff types keep the (permission-gated) admin view.
            default => $this->adminDashboard($request),
        };
    }

    private function adminDashboard(Request $request)
    {
        $data = [

            'finance' => $this->financeData($request),

            'attendance_chart' => $this->attendanceChart(),

            'income_expense_chart' => $this->incomeExpenseChart(),

            'student_info' => $this->studentInfo(),

            'fees_chart' => $this->feesChart(),

            'admins' => User::where('user_type', 'Admin')
                ->where('institute_id', get_institute_id())
                ->count(),

            'teachers' => User::where('user_type', 'Teacher')
                ->where('institute_id', get_institute_id())
                ->count(),

            'students' => User::where('user_type', 'Student')
                ->where('institute_id', get_institute_id())
                ->count(),

            'meetings' => ZoomMeeting::with(['teacher:id,name', 'class:id,class_name', 'section:id,section_name'])
                ->select(['id', 'teacher_id', 'class_id', 'section_id', 'topic', 'start_time', 'join_url'])
                ->latest()
                ->get(),

            'notices' => Notice::where('institute_id', get_institute_id())->get(),
        ];

        return view('backend.dashboard.super_admin', compact('data'));
    }

    private function teacherDashboard()
    {
        $data = $this->teacherStats();

        return view('backend.dashboard.teacher', $data);
    }

    private function studentDashboard()
    {
        $student = auth()->user();

        $data = $this->studentStats($student);

        return view('backend.dashboard.student', $data);
    }

    private function financeData(Request $request)
    {
        $today = now();

        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->endOfMonth()->toDateString();

        // Base query builder (reusable pattern)
        $query = AccountTransactionDetail::query();

        $monthly = (clone $query)
            ->selectRaw('COALESCE(SUM(credit),0) as income, COALESCE(SUM(debit),0) as expense')
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->first();

        $todayData = (clone $query)
            ->selectRaw('COALESCE(SUM(credit),0) as income, COALESCE(SUM(debit),0) as expense')
            ->whereDate('transaction_date', $today)
            ->first();

        return [
            'today_income' => (float) ($todayData->income ?? 0),
            'today_expense' => (float) ($todayData->expense ?? 0),

            'monthly_income' => (float) ($monthly->income ?? 0),
            'monthly_expense' => (float) ($monthly->expense ?? 0),

            'profit_today' => (float) (($todayData->income ?? 0) - ($todayData->expense ?? 0)),
            'profit_monthly' => (float) (($monthly->income ?? 0) - ($monthly->expense ?? 0)),
        ];
    }

    private function attendanceChart(): array
    {
        $labels = [];
        $presentData = [];
        $absentData = [];

        for ($i = 6; $i >= 0; $i--) {

            $date = Carbon::today()->subDays($i);

            $labels[] = $date->format('D');

            $presentData[] = StudentAttendance::where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->whereDate('date', $date)
                ->where('attendance', 1)
                ->count();

            $absentData[] = StudentAttendance::where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->whereDate('date', $date)
                ->where('attendance', 2)
                ->count();
        }

        return [
            'labels' => $labels,
            'present' => $presentData,
            'absent' => $absentData,
        ];
    }

    private function incomeExpenseChart(): array
    {
        $labels = [];
        $incomeData = [];
        $expenseData = [];

        for ($month = 1; $month <= 12; $month++) {

            $labels[] = Carbon::create()->month($month)->format('M');

            $incomeData[] = AccountTransactionDetail::where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->whereYear('transaction_date', now()->year)
                ->whereMonth('transaction_date', $month)
                ->sum('credit');

            $expenseData[] = AccountTransactionDetail::where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->whereYear('transaction_date', now()->year)
                ->whereMonth('transaction_date', $month)
                ->sum('debit');
        }

        return [
            'labels' => $labels,
            'income' => $incomeData,
            'expense' => $expenseData,
        ];
    }

    private function studentInfo(): array
    {
        $totalStudents = Student::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->count();

        $maleStudents = Student::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('gender', 'Male')
            ->count();

        $femaleStudents = Student::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('gender', 'Female')
            ->count();

        return [
            'total_students' => $totalStudents,

            'total_male_students' => $maleStudents,

            'total_female_students' => $femaleStudents,

            'male_percentage' => $totalStudents > 0
                ? round(($maleStudents / $totalStudents) * 100, 2)
                : 0,

            'female_percentage' => $totalStudents > 0
                ? round(($femaleStudents / $totalStudents) * 100, 2)
                : 0,
        ];
    }

    private function feesChart(): array
    {
        $labels = [];
        $collected = [];
        $pending = [];

        for ($month = 1; $month <= 12; $month++) {

            $labels[] = Carbon::create()->month($month)->format('M');

            // Replace with actual fee collection query
            $collected[] = 0;

            // Replace with actual pending fee query
            $pending[] = 0;
        }

        return [
            'labels' => $labels,
            'collected' => $collected,
            'pending' => $pending,
        ];
    }

    private function librarianStats()
    {
        $instituteId = get_institute_id();

        $bookQuery = Book::query()->where('institute_id', $instituteId);
        $issueQuery = BookIssue::query()->where('institute_id', $instituteId);
        $userQuery = User::query()->where('institute_id', $instituteId);

        return [
            // Books
            'total_books' => (clone $bookQuery)->count(),

            // Issue Stats
            'issued_books' => (clone $issueQuery)
                ->where('status', 'Issued')
                ->count(),

            'returned_books' => (clone $issueQuery)
                ->where('status', 'Returned')
                ->count(),

            // Today stats
            'today_issued' => (clone $issueQuery)
                ->whereDate('issue_date', today())
                ->count(),

            'today_returned' => (clone $issueQuery)
                ->whereDate('return_date', today())
                ->count(),

            // Students
            'students' => (clone $userQuery)
                ->where('user_type', 'Student')
                ->count(),
        ];
    }

    private function teacherStats()
    {
        $today = today();
        $instituteId = get_institute_id();

        // Attendance base query (optimized reuse)
        $attendanceQuery = StudentAttendance::query()
            ->whereDate('date', $today)
            ->where('institute_id', $instituteId);

        return [
            // Students
            'students' => Student::where('institute_id', $instituteId)->count(),

            // Attendance summary
            'today_attendance' => (clone $attendanceQuery)->count(),

            'present_students' => (clone $attendanceQuery)
                ->where('attendance', 1)
                ->count(),

            'absent_students' => (clone $attendanceQuery)
                ->where('attendance', 2)
                ->count(),

            // Teacher timetable (NEW FEATURE)
            'today_routines' => $this->teacherTodayRoutine(),
        ];
    }

    private function teacherTodayRoutine()
    {
        $todayName = strtolower(today()->format('l')); // monday, tuesday...

        return ClassRoutine::with(['class', 'section', 'subject'])
            ->where('teacher_id', getUserId())
            ->where('day', $todayName)
            ->where('institute_id', get_institute_id())
            ->orderBy('start_time')
            ->get();
    }

    private function studentStats($student)
    {
        $attendanceQuery = StudentAttendance::query()
            ->where('student_id', $student->id);

        return [
            // Attendance
            'attendance' => (clone $attendanceQuery)->count(),

            'present' => (clone $attendanceQuery)
                ->where('attendance', 1)
                ->count(),

            'absent' => (clone $attendanceQuery)
                ->where('attendance', 2)
                ->count(),

            'today_attendance' => (clone $attendanceQuery)
                ->whereDate('date', today())
                ->first(),

            // Academic placeholders (expandable)
            'fees_due' => 0,
            'upcoming_exams' => [],
            'assignments' => [],

            // ⭐ NEW: CLASS ROUTINE
            'today_routines' => $this->studentTodayRoutine($student),
        ];
    }

    private function studentTodayRoutine($student)
    {
        $todayName = strtolower(today()->format('l')); // monday, tuesday...

        return ClassRoutine::with(['class', 'section', 'subject'])
            ->where('class_id', $student->class_id ?? null)
            ->where('section_id', $student->section_id ?? null)
            ->where('day', $todayName)
            ->orderBy('start_time')
            ->get();
    }
}
