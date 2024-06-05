<?php

use App\Http\Middleware\CheckInstallation;
use App\Http\Middleware\CheckInstallationStatus;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\EnforceDemoMode;
use App\Http\Middleware\EnforcePlanLimit;
use App\Http\Middleware\EnsureActiveInstitute;
use App\Http\Middleware\EnsureAnyPermission;
use App\Http\Middleware\EnsureModuleEnabled;
use App\Http\Middleware\EnsureSaasAdmin;
use App\Http\Middleware\ParentModelClass;
use App\Http\Middleware\RejectPortalApiUser;
use App\Http\Middleware\RejectPortalWebUser;
use App\Http\Middleware\Student;
use App\Http\Middleware\Teacher;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            if (file_exists(base_path('routes/system.php'))) {
                Route::middleware('web')
                    ->group(base_path('routes/system.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->alias([
            'parent' => ParentModelClass::class,
            'teacher' => Teacher::class,
            'student' => Student::class,
            'checkInstallation' => CheckInstallation::class,
            'checkInstallationStatus' => CheckInstallationStatus::class,
            'check.subscription' => CheckSubscription::class,
            'plan.limit' => EnforcePlanLimit::class,
            'demo.restrict' => EnforceDemoMode::class,
            'module.enabled' => EnsureModuleEnabled::class,
            'saas.admin' => EnsureSaasAdmin::class,
            'institute.active' => EnsureActiveInstitute::class,
            'access' => EnsureAnyPermission::class,
        ]);

        $middleware->append([
            HandleCors::class,
        ]);

        // Keep a portal user (Teacher/Student/Parent) out of the institute
        // back-office Blade pages they could otherwise reach by URL after
        // signing in through the shared /login form. Portal routes and shared
        // post-login routes are exempted inside the middleware.
        $middleware->appendToGroup('web', RejectPortalWebUser::class);

        // A deactivated tenant must not be able to use the API at all. Appended
        // to the api group so it covers every module's routes without touching
        // 21 route files; it self-skips for public endpoints and SAAS Admins.
        $middleware->appendToGroup('api', EnsureActiveInstitute::class);

        // Portal accounts (Teacher/Student/Parent) get an ordinary Passport token
        // from the shared /api/login. This blocks that token from every
        // back-office API endpoint; dedicated portal routes (which carry the
        // student/teacher/parent route middleware) and public routes are exempt.
        $middleware->appendToGroup('api', RejectPortalApiUser::class);

        $middleware->preventRequestForgery(except: [
            '/pay-via-ajax',
            '/success',
            'payment/sslcommerz/*',
            '/cancel',
            '/fail',
            '/ipn',
            '/bkash/*',
            '/paytabs-response',
            '/customer/choose-shipping-address',
            '/system_settings',
            '/paytm*',
            'payment/paytabs/callback*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Any request under /api/* is an API client: always answer with a JSON
        // error envelope (401/403/404/422/500) instead of redirecting an
        // unauthenticated caller to the web login route.
        $exceptions->shouldRenderJsonWhen(
            fn ($request, $e) => $request->is('api/*') || $request->expectsJson()
        );
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('demo:reset')->hourly();
        $schedule->command('backup:run')->daily();

        // Notification delivery. These commands existed but were registered on no
        // schedule, so the retry loop SendWhatsAppJob documents as "the single retry
        // loop" never ran: a failed message stayed 'failed' forever and
        // WhatsAppService::MAX_RETRIES was dead. Bulk SMS was worse — the compose
        // screen writes SmsLog rows directly without dispatching a job, so those
        // messages were never sent at all.
        //
        // Both sub-commands are capped per run (--limit) and stream with chunkById,
        // so a large accumulated backlog drains over successive minutes instead of
        // being hydrated in one process.
        $schedule->command('notifications:process')
            ->everyMinute()
            ->withoutOverlapping(10)
            ->runInBackground();

        // CBT: close out attempts whose timer expired while the student was away.
        $schedule->command('online-exams:auto-submit')
            ->everyMinute()
            ->withoutOverlapping(5);
    })->create();
