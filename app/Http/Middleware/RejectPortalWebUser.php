<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Web (Blade) counterpart of {@see RejectPortalApiUser}.
 *
 * The institute back-office pages (Academic, Finance, Examination, user/role
 * management, …) are guarded only by the `auth` session middleware, and most of
 * their controllers do not call `checkPermission()`. A Teacher / Student /
 * Parent who signs in through the shared `/login` form therefore gets a session
 * that can reach those pages directly by URL.
 *
 * Appended to the `web` group. A portal user is bounced back to their own panel
 * on every route EXCEPT: their own portal routes (identified by the
 * student/teacher/parent route middleware) and the handful of shared
 * post-login routes below.
 */
class RejectPortalWebUser
{
    public const PORTAL_USER_TYPES = ['Student', 'Teacher', 'Parent'];

    private const PORTAL_ROUTE_MIDDLEWARE = ['student', 'teacher', 'parent'];

    /** Shared routes every authenticated user may reach regardless of type. */
    private const ALLOWED_ROUTE_NAMES = [
        'dashboard',
        'logout',
        'profile.edit', 'profile.update', 'profile.destroy',
        'language.switch',
        'password.confirm', 'password.update',
        'verification.notice', 'verification.verify', 'verification.send',
        'impersonate.stop', 'impersonate.consume',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! in_array($user->user_type, self::PORTAL_USER_TYPES, true)) {
            return $next($request);
        }

        $route = $request->route();

        if ($route) {
            if (in_array($route->getName(), self::ALLOWED_ROUTE_NAMES, true)) {
                return $next($request);
            }

            foreach ($route->gatherMiddleware() as $middleware) {
                if (! is_string($middleware)) {
                    continue;
                }
                if (in_array(explode(':', $middleware, 2)[0], self::PORTAL_ROUTE_MIDDLEWARE, true)) {
                    return $next($request);
                }
            }
        }

        $target = $user->user_type === 'Parent' ? route('parent.dashboard') : url('/dashboard');

        return redirect($target)->with('error', _lang('You are not authorized to access this feature.'));
    }
}
