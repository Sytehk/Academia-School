<?php

namespace Modules\Authentication\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\StaffAttendance;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\StudentAttendance;
use Modules\Accounting\Models\AccountTransactionDetail;
use Modules\Authentication\Http\Requests\Settings\MailConfigUpdateRequest;
use Modules\Authentication\Http\Requests\Settings\SAASSettingUpdateRequest;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\InstituteType;
use Modules\Authentication\Models\Plan;
use Modules\Authentication\Models\SAASBanner;
use Modules\Authentication\Models\SAASBenefit;
use Modules\Authentication\Models\SAASCore;
use Modules\Authentication\Models\SAASFaq;
use Modules\Authentication\Models\SAASFeedback;
use Modules\Authentication\Models\SAASPanel;
use Modules\Authentication\Models\SAASSetting;
use Modules\Authentication\Models\SAASSolution;
use Modules\Authentication\Models\Subscription;
use Modules\Authentication\Models\SubscriptionItem;
use Modules\Authentication\Models\User;
use Modules\Frontend\Models\SAASPolicy;
use Modules\Library\Models\Book;

class DashboardController extends Controller
{
    public function getBanners(Request $request): JsonResponse
    {
        $data = SAASBanner::query()
            ->where('status', 1)
            ->latest()
            ->get();

        return $this->responseSuccess($data, 'Banners fetched successfully.');
    }

    public function getPackages(Request $request): JsonResponse
    {
        $data = Plan::query()
            ->where('status', 1)
            ->latest()
            ->get();

        return $this->responseSuccess($data, 'Packages fetched successfully.');
    }

    public function getPanels(Request $request): JsonResponse
    {
        $data = SAASPanel::query()
            ->where('status', 1)
            ->latest()
            ->get();

        return $this->responseSuccess($data, 'Panels fetched successfully.');
    }

    public function getCores(Request $request): JsonResponse
    {
        $data = SAASCore::query()
            ->where('status', 1)
            ->latest()
            ->get();

        return $this->responseSuccess($data, 'Cores fetched successfully.');
    }

    public function getBenefits(Request $request): JsonResponse
    {
        $data = SAASBenefit::query()
            ->where('status', 1)
            ->latest()
            ->get();

        return $this->responseSuccess($data, 'Benefits fetched successfully.');
    }

    public function getSolutions(Request $request): JsonResponse
    {
        $data = SAASSolution::query()
            ->where('status', 1)
            ->latest()
            ->get();

        return $this->responseSuccess($data, 'Solutions fetched successfully.');
    }

    public function getFeedback(Request $request): JsonResponse
    {
        $data = SAASFeedback::query()
            ->where('status', 1)
            ->latest()
            ->get();

        return $this->responseSuccess($data, 'Feedback fetched successfully.');
    }

    public function getFaqs(Request $request): JsonResponse
    {
        $data = SAASFaq::query()
            ->where('status', 1)
            ->latest()
            ->get();

        return $this->responseSuccess($data, 'Faqs fetched successfully.');
    }

    public function saasPolicy(Request $request)
    {
        $request->validate([
            'type' => 'required|in:privacy,cookie,terms',
        ]);

        $data = SAASPolicy::select('id', 'type', 'description')
            ->where('type', $request->type)
            ->whereNull('deleted_at') // optional if using SoftDeletes
            ->latest() // order by created_at desc
            ->first();

        return $this->responseSuccess(
            $data,
            'SAAS Policy has been fetched successfully.'
        );
    }

    public function saasInstituteType(Request $request): JsonResponse
    {
        $data = InstituteType::query()
            ->where('status', 1)
            ->latest()
            ->get();

        return $this->responseSuccess($data, 'Institute Type fetched successfully.');
    }

    public function index(Request $request): JsonResponse
    {
        $today = Carbon::now()->toDateString();
        // Collecting counts of different entities efficiently
        $fromDate = $request->from_date ?? date('Y-m-01');
        $toDate = $request->to_date ?? date('Y-m-t');

        // Fetch total income and expense
        $totals = AccountTransactionDetail::select([
            DB::raw('SUM(CASE WHEN credit > debit THEN credit ELSE 0 END) as total_income'),
            DB::raw('SUM(CASE WHEN debit > credit THEN debit ELSE 0 END) as total_expense'),
        ])
            ->join('accounting_ledgers', 'account_transaction_details.ledger_id', '=', 'accounting_ledgers.id')
            ->where('account_transaction_details.institute_id', get_institute_id())
            ->where('account_transaction_details.branch_id', get_branch_id())
            ->whereBetween('account_transaction_details.transaction_date', [$fromDate, $toDate])
            ->first();

        // Get today's date
        $today = Carbon::today()->toDateString();
        $today_expense_income = AccountTransactionDetail::select([
            DB::raw('SUM(CASE WHEN credit > debit THEN credit ELSE 0 END) as today_income'),
            DB::raw('SUM(CASE WHEN debit > credit THEN debit ELSE 0 END) as today_expense'),
        ])
            ->join('accounting_ledgers', 'account_transaction_details.ledger_id', '=', 'accounting_ledgers.id')
            ->where('account_transaction_details.institute_id', get_institute_id())
            ->where('account_transaction_details.branch_id', get_branch_id())
            ->whereDate('account_transaction_details.transaction_date', $today) // Filter for today only
            ->first();

        $totalStudents = Student::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->count();
        $totalMaleStudents = Student::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->where('gender', 'Male')->count();
        $totalFemaleStudents = Student::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->where('gender', 'Female')->count();
        $data = [
            'fees_awaiting_payment' => [
                'total' => 59,
                'waiting' => 8,
            ],

            'converted_leads' => [
                'total' => 10,
                'waiting' => 2,
            ],

            'staff_present_today' => [
                'attendance_count' => User::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->where('user_type', 'Staff')->count(),
                'present_count' => StaffAttendance::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->where('type', 'Staff')->where('date', $today)->count(),
            ],

            'student_present_today' => [
                'attendance_count' => User::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->where('user_type', 'Student')->count(),
                'present_count' => StudentAttendance::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->where('period_id', 1)->where('attendance', 1)->where('date', $today)->count(),
            ],

            'fees_overview' => [
                'unpaid' => 41,
                'partial' => 10,
                'paid' => 8,
            ],

            'enquiry_overview' => [
                'active_student' => User::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->where('user_type', 'Student')->where('user_status', '1')->count(),
                'own_student' => 2,
                'passive' => 1,
                'lost' => User::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->where('user_type', 'Student')->where('user_status', '0')->count(),
                'dead' => 1,
            ],

            'library_overview' => [
                'due_for_return' => 34,
                'returned' => 11,
                'issued_out_of' => 33,
                'available_for_out' => 497,
                'books' => Book::count(),
            ],

            'student_today_present' => [
                'present' => $presentCount = StudentAttendance::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->where('period_id', 1)
                    ->where('attendance', 1)
                    ->where('date', $today)
                    ->count(),
                'present_percentage' => $presentCount > 0
                    ? round(($presentCount / max(1, User::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->where('user_type', 'Student')->count())) * 100, 2)
                    : 0,

                'absent' => $absentCount = StudentAttendance::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->where('period_id', 1)
                    ->where('attendance', 2)
                    ->where('date', $today)
                    ->count(),
                'absent_percentage' => $absentCount > 0
                    ? round(($absentCount / max(1, User::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->where('user_type', 'Student')->count())) * 100, 2)
                    : 0,
            ],

            'student_info' => [
                'total_students' => $totalStudents,
                'total_male_students' => $totalMaleStudents,
                'male_percentage' => $totalStudents > 0 ? round(($totalMaleStudents / $totalStudents) * 100, 2) : 0,
                'total_female_students' => $totalFemaleStudents,
                'female_percentage' => $totalStudents > 0 ? round(($totalFemaleStudents / $totalStudents) * 100, 2) : 0,
            ],

            'today_income' => $today_expense_income->today_income ?? 0,
            'today_expense' => $today_expense_income->today_expense ?? 0,
            'monthly_income' => $totals->total_income ?? 0,
            'monthly_expense' => $totals->total_expense ?? 0,

            'admins' => User::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->where('user_type', 'Admin')->count(),
            'staffs' => User::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->whereNotIn('user_type', [
                'Admin',
            ])
                ->count(),
            'teachers' => User::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->where('user_type', 'Teacher')->count(),
            'students' => User::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->where('user_type', 'Student')->count(),
        ];

        return $this->responseSuccess(
            $data,
            _lang('Dashboard data has been fetched successfully.')
        );
    }

    public function studentAttendanceSummary(Request $request): JsonResponse
    {
        $instituteId = get_institute_id();
        $branchId = get_branch_id();
        $type = $request->type; // weekly, half_month, monthly
        $today = Carbon::today();

        // Determine the date range based on type
        if ($type === 'weekly') {
            $startDate = $today->copy()->startOfWeek();
            $endDate = $today->copy()->endOfWeek();
        } elseif ($type === 'half_month') {
            $startDate = $today->day <= 15 ? $today->copy()->startOfMonth() : $today->copy()->startOfMonth()->addDays(15);
            $endDate = $today->day <= 15 ? $startDate->copy()->addDays(14) : $today->copy()->endOfMonth();
        } else { // Monthly
            $startDate = $today->copy()->startOfMonth();
            $endDate = $today->copy()->endOfMonth();
        }

        // Get total students count (to calculate percentage)
        $totalStudents = User::where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->where('user_type', 'Student')
            ->count();

        // Fetch attendance data
        $attendanceData = StudentAttendance::where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->whereBetween('date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(date) as attendance_date'),
                DB::raw('SUM(attendance = "1") as present_count'),
                DB::raw('SUM(attendance = "2") as absent_count')
            )
            ->groupBy('attendance_date')
            ->orderBy('attendance_date', 'asc')
            ->get()
            ->keyBy('attendance_date');

        // Generate response with all dates
        $result = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $data = $attendanceData->get($dateStr);

            $present = $data->present_count ?? 0;
            $absent = $data->absent_count ?? 0;

            $result[] = [
                'date' => $dateStr,
                'present' => $present,
                'present_percentage' => $totalStudents > 0 ? round(($present / $totalStudents) * 100, 2) : 0,
                'absent' => $absent,
                'absent_percentage' => $totalStudents > 0 ? round(($absent / $totalStudents) * 100, 2) : 0,
            ];
        }

        return $this->responseSuccess($result, _lang('Attendance report generated successfully.'));
    }

    public function saasDashboardData(Request $request): JsonResponse
    {
        $year = $request->integer('year', now()->year);

        /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

        $totalInstituteCount = Institute::count();

        $totalActiveInstitute = Institute::where('status', 1)->count();

        // Deactivating an institute writes 0 (InstituteController::statusUpdate)
        // while the schema documented 2, so `= 2` never matched a real row and
        // this always reported zero. "Not active" holds under both conventions.
        $totalInactiveInstitute = Institute::where('status', '!=', 1)->count();

        $totalPlans = Plan::count();

        $totalActiveSubscriptions = Subscription::where('status', 'active')->count();

        $totalExpiredSubscriptions = Subscription::where('status', 'expired')->count();

        $totalPendingSubscriptions = Subscription::where('status', 'pending')->count();

        $totalRevenue = SubscriptionItem::whereNull('deleted_at')
            ->sum('amount_paid');

        $currentMonthRevenue = SubscriptionItem::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->whereNull('deleted_at')
            ->sum('amount_paid');

        $lastMonthRevenue = SubscriptionItem::whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereNull('deleted_at')
            ->sum('amount_paid');

        $revenueGrowth = $lastMonthRevenue > 0
            ? round((($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 2)
            : 0;

        /*
    |--------------------------------------------------------------------------
    | Monthly Revenue Chart
    |--------------------------------------------------------------------------
    */

        $monthlyRevenue = SubscriptionItem::selectRaw('
            MONTH(created_at) as month,
            SUM(amount_paid) as revenue
        ')
            ->whereYear('created_at', $year)
            ->whereNull('deleted_at')
            ->groupByRaw('MONTH(created_at)')
            ->pluck('revenue', 'month');

        $revenueChart = collect(range(1, 12))->map(function ($month) use ($monthlyRevenue) {
            return [
                'month' => Carbon::create()->month($month)->format('M'),
                'revenue' => (float) $monthlyRevenue->get($month, 0),
            ];
        });

        /*
    |--------------------------------------------------------------------------
    | Monthly Institute Registration
    |--------------------------------------------------------------------------
    */

        $monthlyInstitutes = Institute::selectRaw('
            MONTH(created_at) as month,
            COUNT(*) as total
        ')
            ->whereYear('created_at', $year)
            ->groupByRaw('MONTH(created_at)')
            ->pluck('total', 'month');

        $instituteChart = collect(range(1, 12))->map(function ($month) use ($monthlyInstitutes) {
            return [
                'month' => Carbon::create()->month($month)->format('M'),
                'institutes' => (int) $monthlyInstitutes->get($month, 0),
            ];
        });

        /*
    |--------------------------------------------------------------------------
    | Last 3 Months Subscription Trend
    |--------------------------------------------------------------------------
    */

        $subscriptionTrend = collect();

        for ($i = 2; $i >= 0; $i--) {

            $date = now()->subMonths($i);

            $subscriptionTrend->push([
                'month' => $date->format('M'),

                'subscriptions' => Subscription::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),

                'revenue' => (float) SubscriptionItem::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->whereNull('deleted_at')
                    ->sum('amount_paid'),
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Plan Distribution
    |--------------------------------------------------------------------------
    */

        $activeInstituteCount = Subscription::where('status', 'active')
            ->whereNull('deleted_at')
            ->distinct('institute_id')
            ->count('institute_id');

        $planDistribution = Subscription::join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->where('subscriptions.status', 'active')
            ->whereNull('subscriptions.deleted_at')
            ->select(
                'plans.id',
                'plans.name',
                DB::raw('COUNT(DISTINCT subscriptions.institute_id) as institute_count')
            )
            ->groupBy('plans.id', 'plans.name')
            ->get()
            ->map(function ($item) use ($activeInstituteCount) {
                return [
                    'plan_id' => $item->id,
                    'plan_name' => $item->name,
                    'institute_count' => (int) $item->institute_count,
                    'usage_percent' => $activeInstituteCount > 0
                        ? round(($item->institute_count / $activeInstituteCount) * 100, 2)
                        : 0,
                ];
            });

        /*
    |--------------------------------------------------------------------------
    | Latest 5 Subscriptions
    |--------------------------------------------------------------------------
    */

        $latestSubscriptions = Subscription::with([
            'plan:id,name',
            'institute:id,name,email,logo',
        ])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($subscription) {

                return [
                    'subscription_id' => $subscription->id,
                    'institute' => $subscription->institute?->name,
                    'logo' => $subscription->institute?->logo,
                    'email' => $subscription->institute?->email,
                    'plan' => $subscription->plan?->name,
                    'status' => $subscription->status,
                    'start_date' => $subscription->start_date,
                    'end_date' => $subscription->end_date,
                    'created_at' => $subscription->created_at?->diffForHumans(),
                ];
            });

        /*
    |--------------------------------------------------------------------------
    | Expiring Subscriptions (Next 30 Days)
    |--------------------------------------------------------------------------
    */

        $expiringSubscriptions = Subscription::with([
            'plan:id,name',
            'institute:id,name,email,logo',
        ])
            ->whereDate('end_date', '>=', today())
            ->whereDate('end_date', '<=', today()->addDays(30))
            ->orderBy('end_date')
            ->take(10)
            ->get();

        /*
    |--------------------------------------------------------------------------
    | Recent Payments
    |--------------------------------------------------------------------------
    */

        $recentPayments = SubscriptionItem::with([
            'subscription.institute:id,name',
            'subscription.plan:id,name',
        ])
            ->latest()
            ->take(10)
            ->get();

        /*
    |--------------------------------------------------------------------------
    | Top 10 Paying Institutes
    |--------------------------------------------------------------------------
    */

        $topInstitutes = SubscriptionItem::join(
            'subscriptions',
            'subscriptions.id',
            '=',
            'subscription_items.subscription_id'
        )
            ->join(
                'institutes',
                'institutes.id',
                '=',
                'subscriptions.institute_id'
            )
            ->select(
                'institutes.id',
                'institutes.name',
                DB::raw('SUM(subscription_items.amount_paid) as revenue')
            )
            ->whereNull('subscription_items.deleted_at')
            // Only active, non-deleted institutes.
            ->whereNull('institutes.deleted_at')
            ->where('institutes.status', 1)
            ->groupBy('institutes.id', 'institutes.name')
            ->orderByDesc('revenue')
            ->take(10)
            ->get();

        /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

        return $this->responseSuccess([

            'statistics' => [

                'total_institutes' => $totalInstituteCount,
                'active_institutes' => $totalActiveInstitute,
                'inactive_institutes' => $totalInactiveInstitute,

                'total_plans' => $totalPlans,

                'active_subscriptions' => $totalActiveSubscriptions,
                'expired_subscriptions' => $totalExpiredSubscriptions,
                'pending_subscriptions' => $totalPendingSubscriptions,

                'subscription_institutes' => $activeInstituteCount,

                'total_revenue' => (float) $totalRevenue,
                'current_month_revenue' => (float) $currentMonthRevenue,
                'last_month_revenue' => (float) $lastMonthRevenue,
                'revenue_growth' => $revenueGrowth,
            ],

            'charts' => [

                'monthly_revenue' => $revenueChart,

                'monthly_institutes' => $instituteChart,

                'subscription_trend' => $subscriptionTrend,

                'plan_distribution' => $planDistribution,
            ],

            'latest_subscriptions' => $latestSubscriptions,

            'expiring_subscriptions' => $expiringSubscriptions,

            'recent_payments' => $recentPayments,

            'top_paying_institutes' => $topInstitutes,

        ], _lang('SaaS dashboard data fetched successfully.'));
    }

    public function getSAASSettings(Request $request)
    {
        $saas_settings = DB::table('s_a_a_s_settings')->pluck('value', 'name')->toArray();

        return $this->responseSuccess($saas_settings, _lang('SaaS settings fetched successfully.'));
    }

    public function saasSettingsUpdate(SAASSettingUpdateRequest $request): JsonResponse
    {
        try {
            // If POST request, update or create settings
            DB::beginTransaction();
            foreach ($request->validated() as $key => $value) {
                if ($key === '_token') {
                    continue;
                }

                SAASSetting::updateOrInsert(
                    [
                        'name' => $key,
                    ],
                    [
                        'value' => $value,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
            DB::commit();

            return $this->responseSuccess([], _lang('SAAS Settings have been successfully updated.'));
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->responseError(['error' => $e->getMessage()], _lang('Failed to store records unsuccessfully.'));
        }
    }

    public function updateMailConfig(MailConfigUpdateRequest $request)
    {
        foreach ($request->validated() as $key => $value) {
            setEnvValue($key, $value);
        }

        // Clear config cache so changes apply immediately
        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        return $this->responseSuccess(
            [],
            _lang('Mail configuration updated successfully.')
        );
    }

    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg|max:8192',
        ]);

        $imageUrl = null;
        if (isset($request['logo'])) {
            if (isset($request['logo']) && $request['logo']->isValid()) {
                $imageUrl = fileUploader('logos/', 'png', $request['logo']);
            }
        }

        // Prepare data
        $data = [
            'name' => 'logo',
            'value' => $imageUrl,
            'updated_at' => Carbon::now(),
        ];

        // Update or create logo setting
        $saasSetting = SAASSetting::updateOrCreate(
            [
                'name' => 'logo',
            ],
            $data
        );

        return $this->responseSuccess(
            $saasSetting,
            _lang('SAAS settings logo have been successfully updated')
        );
    }

    public function getMailConfig()
    {
        $mailConfig = [
            'MAIL_MAILER' => env('MAIL_MAILER'),
            'MAIL_HOST' => env('MAIL_HOST'),
            'MAIL_PORT' => env('MAIL_PORT'),
            'MAIL_USERNAME' => env('MAIL_USERNAME'),
            'MAIL_PASSWORD' => env('MAIL_PASSWORD') ? '********' : null, // mask password
            'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
            'MAIL_FROM_NAME' => env('MAIL_FROM_NAME'),
        ];

        return $this->responseSuccess(
            $mailConfig,
            _lang('Mail configuration fetched successfully.')
        );
    }
}
