<?php

namespace Modules\Authentication\Http\Controllers\API;

use App\Helpers\DomainHelper;
use App\Http\Controllers\Controller;
use App\Traits\Authenticatable;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\Plan;
use Modules\Authentication\Models\Subscription;
use Modules\Authentication\Models\SubscriptionItem;
use Modules\Authentication\Models\User;

class UtilityController extends Controller
{
    use Authenticatable;

    public function linkStorage()
    {
        $publicStorage = public_path('storage');
        $target = storage_path('app/public');

        try {
            if (! File::exists($publicStorage)) {
                File::link($target, $publicStorage);

                return response()->json(['status' => 'success', 'message' => 'Storage link created.']);
            } else {
                return response()->json(['status' => 'info', 'message' => 'Storage link already exists.']);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function unlinkStorage()
    {
        $publicStorage = public_path('storage');

        try {
            if (is_link($publicStorage)) {
                unlink($publicStorage);
            } elseif (is_dir($publicStorage)) {
                // Attempt to delete via Laravel
                if (! File::deleteDirectory($publicStorage)) {
                    // Fallback for stubborn directories
                    exec('rm -rf '.escapeshellarg($publicStorage));
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'The storage link or directory has been removed.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed: '.$e->getMessage(),
            ]);
        }
    }

    public function clearAllCache()
    {
        try {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            return response()->json(['status' => 'success', 'message' => 'All caches cleared.']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function allStudentsGet(Request $request)
    {
        $students = DB::table('student_sessions as ss')
            ->join('students as s', 's.id', '=', 'ss.student_id')
            ->select(
                'ss.qr_code',
                's.first_name',
                's.last_name'
            )
            ->whereNotNull('ss.qr_code')
            ->get();

        return $this->responseSuccess(
            $students,
            _lang('Students with QR code fetched successfully.')
        );
    }

    public function domainCheck(Request $request): JsonResponse
    {
        $domain = $request->query('domain');
        $info = DomainHelper::getDomainInfo($domain);

        // Add expiration warning
        $warning = DomainHelper::expirationWarning($info['expiration'] ?? null);
        $info['expiration_warning'] = $warning;

        return response()->json([
            'success' => true,
            'data' => $info,
        ]);
    }

    public function saasAccountActivation()
    {
        // check already created saas user
        $user = User::where(
            'email',
            'saasadmin@gmail.com'
        )->first();

        if ($user) {
            return response()->json([
                'success' => true,
                'message' => 'SAAS Already Activated',
            ]);
        }

        // create first saas user
        User::create([
            'name' => 'SAAS Admin',
            'email' => 'saasadmin@gmail.com',
            'phone' => '00000000000',
            'password' => Hash::make('12345678'),
            'user_type' => 'SAAS Admin',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'SAAS Activated Successfully',
        ]);
    }

    public function saasAccountStatus()
    {
        $isActivated = User::where('email', 'saasadmin@gmail.com')->exists();

        return response()->json([
            'success' => true,
            'message' => $isActivated
                ? 'SAAS Already Activated'
                : 'SAAS Not Activated',
            'data' => [
                'activated' => $isActivated,
                'show_activation_button' => ! $isActivated,
            ],
        ]);
    }

    public function saasStats(Request $request)
    {
        $totalInstituteCount = Institute::count();
        $totalActiveInstitute = Institute::where('status', 1)->count();
        // See DashboardController: deactivation writes 0, not the schema's 2.
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

        return $this->responseSuccess([
            'total_institutes' => $totalInstituteCount,
            'active_institutes' => $totalActiveInstitute,
            'inactive_institutes' => $totalInactiveInstitute,
            'total_plans' => $totalPlans,
            'active_subscriptions' => $totalActiveSubscriptions,
            'expired_subscriptions' => $totalExpiredSubscriptions,
            'pending_subscriptions' => $totalPendingSubscriptions,
            'total_revenue' => (float) $totalRevenue,
            'current_month_revenue' => (float) $currentMonthRevenue,
            'last_month_revenue' => (float) $lastMonthRevenue,
            'revenue_growth' => $revenueGrowth,
        ], _lang('SaaS Stats data fetched successfully.'));
    }
}
