<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Authentication\Http\Controllers\API\AuthController;
use Modules\Authentication\Http\Controllers\API\BranchController;
use Modules\Authentication\Http\Controllers\API\CurrencyController;
use Modules\Authentication\Http\Controllers\API\DashboardController;
use Modules\Authentication\Http\Controllers\API\DomainRequestController;
use Modules\Authentication\Http\Controllers\API\ImpersonationController;
use Modules\Authentication\Http\Controllers\API\InstituteController;
use Modules\Authentication\Http\Controllers\API\InstituteImageSAASSettingController;
use Modules\Authentication\Http\Controllers\API\InstituteImageSettingController;
use Modules\Authentication\Http\Controllers\API\InstituteTypeController;
use Modules\Authentication\Http\Controllers\API\OnboardingsController;
use Modules\Authentication\Http\Controllers\API\PermissionController;
use Modules\Authentication\Http\Controllers\API\PlanController;
use Modules\Authentication\Http\Controllers\API\RoleController;
use Modules\Authentication\Http\Controllers\API\SAASBannerController;
use Modules\Authentication\Http\Controllers\API\SAASBenefitController;
use Modules\Authentication\Http\Controllers\API\SAASCoreController;
use Modules\Authentication\Http\Controllers\API\SAASFaqController;
use Modules\Authentication\Http\Controllers\API\SAASFeedbackController;
use Modules\Authentication\Http\Controllers\API\SAASPanelController;
use Modules\Authentication\Http\Controllers\API\SAASRoleController;
use Modules\Authentication\Http\Controllers\API\SAASSolutionController;
use Modules\Authentication\Http\Controllers\API\SAASSubscriptionController;
use Modules\Authentication\Http\Controllers\API\UserController;
use Modules\Authentication\Http\Controllers\API\UtilityController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(AuthController::class)->middleware('throttle:6,1')->group(function () {
    Route::post('/login', 'login');
    Route::post('/saas-login', 'saasLogin');
    Route::post('/register', 'register');
});

Route::middleware('auth:api')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::get('/profile', 'user');
        Route::post('/logout', 'logout');
        Route::post('/change-password', 'changePassword');
        Route::post('/profile-update', 'profileUpdate');
    });

    Route::apiResource('branches', BranchController::class)->middleware('plan.limit:branch');
    Route::apiResource('permissions', PermissionController::class);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('users', UserController::class);

    Route::get('dashboard-data', [DashboardController::class, 'index']);
    Route::get('student-attendance-summery', [DashboardController::class, 'studentAttendanceSummary']);

    // Institute-initiated subscription actions (the tenant asks; a SaaS admin approves).
    Route::prefix('subscription')->group(function () {
        Route::post('/request-upgrade', [OnboardingsController::class, 'requestUpgrade']);
        Route::post('/payment-upgrade', [OnboardingsController::class, 'paymentSubscriptions']);
    });

    // Institute's own branding.
    Route::apiResource('institute-image-settings', InstituteImageSettingController::class);

    /*
    |--------------------------------------------------------------------------
    | Platform administration — SAAS Admin only
    |--------------------------------------------------------------------------
    |
    | `auth:api` only proves the token is valid, and every institute user gets
    | one at /api/login. These endpoints read or mutate data across every
    | tenant, so they are additionally gated to `user_type = 'SAAS Admin'`.
    | The SaaS panel signs in via /api/saas-login and is unaffected.
    |
    */
    Route::middleware('saas.admin')->group(function () {
        Route::get('institutes-status-update/{instituteId}', [InstituteController::class, 'statusUpdate']);
        Route::apiResource('institutes', InstituteController::class);
        Route::post('institutes/{institute}/impersonate', [ImpersonationController::class, 'start']);

        Route::get('domain-requests', [DomainRequestController::class, 'index']);
        Route::post('domain-requests/{id}/approve', [DomainRequestController::class, 'approve']);
        Route::post('domain-requests/{id}/reject', [DomainRequestController::class, 'reject']);

        Route::apiResource('plans', PlanController::class);
        Route::apiResource('saas-roles', SAASRoleController::class);

        // Onboarding
        Route::get('onboardings-list', [OnboardingsController::class, 'index']);
        Route::get('onboardings-approve/{onboardingId}', [OnboardingsController::class, 'approve']);
        Route::post('/subscription/upgrade', [OnboardingsController::class, 'upgrade']);
        Route::get('subscription/request-upgrade-list', [OnboardingsController::class, 'requestUpgradeList']);
        Route::get('subscription/approve-request/{id}', [OnboardingsController::class, 'approveRequest']);

        Route::apiResource('institute-image-saas-settings', InstituteImageSAASSettingController::class);

        Route::post('saas-settings-update', [DashboardController::class, 'saasSettingsUpdate']);
        Route::post('saas-settings-upload-logo', [DashboardController::class, 'uploadLogo']);
        Route::post('saas-settings-mail-update', [DashboardController::class, 'updateMailConfig']);
        Route::get('saas-settings-mail-config', [DashboardController::class, 'getMailConfig']);

        // SAAS Panel CRUD
        Route::get('saas-dashboard-data', [DashboardController::class, 'saasDashboardData']);
        Route::apiResource('saas-banners', SAASBannerController::class);
        Route::apiResource('saas-panels', SAASPanelController::class);
        Route::apiResource('saas-cores', SAASCoreController::class);
        Route::apiResource('saas-solutions', SAASSolutionController::class);
        Route::apiResource('saas-benefits', SAASBenefitController::class);
        Route::apiResource('saas-feedback', SAASFeedbackController::class);
        Route::apiResource('saas-faqs', SAASFaqController::class);
        Route::apiResource('saas-subscriptions', SAASSubscriptionController::class);
        Route::apiResource('institute-types', InstituteTypeController::class);
        Route::get('currencies-status-update/{id}', [CurrencyController::class, 'statusUpdate']);
        Route::apiResource('currencies', CurrencyController::class);
        Route::get('saas-contacts', [SAASSubscriptionController::class, 'saasContacts']);
    });
});

Route::prefix('saas')->group(function () {
    // SAAS Panel CRUD
    Route::get('banners', [DashboardController::class, 'getBanners']);
    Route::get('packages', [DashboardController::class, 'getPackages']);
    Route::get('panels', [DashboardController::class, 'getPanels']);
    Route::get('cores', [DashboardController::class, 'getCores']);
    Route::get('solutions', [DashboardController::class, 'getSolutions']);
    Route::get('benefits', [DashboardController::class, 'getBenefits']);
    Route::get('feedback', [DashboardController::class, 'getFeedback']);
    Route::get('faqs', [DashboardController::class, 'getFaqs']);
    Route::get('policies', [DashboardController::class, 'saasPolicy']);
    Route::get('institute-types', [DashboardController::class, 'saasInstituteType']);
    Route::post('subscribe', [SAASSubscriptionController::class, 'publicStore']);
    Route::post('contacts', [SAASSubscriptionController::class, 'saasContact']);
});

Route::get('get-saas-settings', [DashboardController::class, 'getSAASSettings']);
Route::get('system/link-storage', [UtilityController::class, 'linkStorage']);
Route::get('system/unlink-storage', [UtilityController::class, 'unlinkStorage']);
Route::get('system/clear-cache', [UtilityController::class, 'clearAllCache']);
Route::get('system/all-students-get', [UtilityController::class, 'allStudentsGet']);
Route::get('v1/system/domain-check', [UtilityController::class, 'domainCheck']);
Route::get('saas-account-activation', [UtilityController::class, 'saasAccountActivation']);
Route::get('saas-account-status', [UtilityController::class, 'saasAccountStatus']);
Route::get('saas-stats', [UtilityController::class, 'saasStats']);
