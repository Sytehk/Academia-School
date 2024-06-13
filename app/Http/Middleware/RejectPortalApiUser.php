<?php

namespace App\Http\Middleware;

use App\Traits\ResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Portal accounts (Teacher / Student / Parent) authenticate through the same
 * `/api/login` endpoint as institute back-office staff and therefore receive an
 * ordinary Passport token that satisfies `auth:api`. Nothing else in the admin
 * API stack checks *who* the caller is, so before this middleware a student's
 * token could drive every institute-admin endpoint (create/delete students,
 * edit fee structures, collect fees manually, rewrite gateway credentials, …),
 * bounded only by the tenant global scope.
 *
 * This middleware is appended to the `api` group. It denies a portal user type
 * on any authenticated API route UNLESS that route is itself a portal route —
 * detected by the presence of the `student` / `teacher` / `parent` route
 * middleware, which every dedicated portal endpoint already carries. Public
 * (unauthenticated) endpoints are untouched: with no resolved user it simply
 * passes through and lets the normal auth flow answer.
 */
class RejectPortalApiUser
{
    use ResponseTrait;

    /** user_type values that belong to a self-service portal, not the back office. */
    public const PORTAL_USER_TYPES = ['Student', 'Teacher', 'Parent'];

    /** Route middleware markers that identify a legitimate portal endpoint. */
    private const PORTAL_ROUTE_MIDDLEWARE = ['student', 'teacher', 'parent'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?: auth('api')->user();

        // Unauthenticated (public routes, or an about-to-fail auth:api route):
        // not our concern — let the auth layer respond.
        if (! $user) {
            return $next($request);
        }

        if (! in_array($user->user_type, self::PORTAL_USER_TYPES, true)) {
            return $next($request);
        }

        // Portal user on a genuine portal route → allowed.
        $routeMiddleware = $request->route()?->gatherMiddleware() ?? [];
        foreach ($routeMiddleware as $middleware) {
            if (! is_string($middleware)) {
                continue;
            }
            $name = explode(':', $middleware, 2)[0];
            if (in_array($name, self::PORTAL_ROUTE_MIDDLEWARE, true)) {
                return $next($request);
            }
        }

        // Portal user reaching a back-office endpoint → hard stop.
        return $this->responseError(
            [],
            _lang('You are not authorized to access this feature.'),
            403
        );
    }
}
