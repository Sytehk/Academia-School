<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Shared gate for the Teacher / Student / Parent portal middleware.
 *
 * Every portal route must, in order:
 *   1. have an authenticated user (previously the middleware NPE'd on a null
 *      user and returned a raw 500 — several portal web groups had no `auth`
 *      middleware at all);
 *   2. be that exact portal user_type;
 *   3. be an active account (status / user_status = 1) — a student or teacher
 *      deactivated by the institute must lose portal access immediately, not
 *      only when their token expires;
 *   4. be bound to a branch — the tenant global scope silently drops the branch
 *      filter when branch_id is null, which would expose every branch's data.
 */
class PortalGate
{
    public static function check(Request $request, Closure $next, string $expectedType, object $responder)
    {
        $user = $request->user() ?: auth('api')->user() ?: auth()->user();

        if (! $user) {
            return $responder->responseError([], _lang('Unauthenticated.'), 401);
        }

        if ($user->user_type !== $expectedType) {
            return $responder->responseError([], _lang('You are not authorized to access this feature.'), 403);
        }

        // `status` is the account enable/disable flag. (`user_status` is a
        // separate student-lifecycle marker — active/lost/etc. — and must NOT
        // be treated as an auth block here.)
        if ((int) ($user->status ?? 0) !== 1) {
            return $responder->responseError([], _lang('Your account is inactive. Please contact the institute administrator.'), 403);
        }

        if (empty($user->branch_id)) {
            return $responder->responseError([], _lang('Your account is not assigned to a branch. Please contact the institute administrator.'), 403);
        }

        return $next($request);
    }
}
