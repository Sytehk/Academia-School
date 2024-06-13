<?php

namespace App\Http\Middleware;

use App\Traits\ResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route-level permission gate: `->middleware('access:perm_a,perm_b')` lets the
 * request through when the user holds ANY of the listed permissions.
 *
 * Built on can_access_system(), so an institute Admin / SAAS Admin keeps full
 * access even on databases whose roles were seeded before a permission existed,
 * while a restricted back-office role (accountant, librarian, ...) is refused
 * features it was never granted.
 */
class EnsureAnyPermission
{
    use ResponseTrait;

    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        foreach ($permissions as $permission) {
            if (can_access_system($permission)) {
                return $next($request);
            }
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->responseError([], _lang('You are not authorized to access this feature.'), 403);
        }

        abort(403);
    }
}
