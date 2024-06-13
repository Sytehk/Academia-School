<?php

namespace App\Http\Middleware;

use App\Traits\ResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Modules\Authentication\Models\Institute;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks API access for a tenant whose institute has been deactivated
 * (institutes.status != 1) by a platform admin, and — when
 * `saas.enforce_subscription` is enabled — for a tenant whose subscription has
 * expired (an institute with no subscription at all still passes, per the
 * platform's documented fail-open convention).
 *
 * Runs in the `api` middleware group, i.e. before route middleware, so it
 * resolves the bearer identity through the api guard itself. When there is no
 * authenticated user (public endpoints, or an about-to-fail `auth:api` route)
 * it does nothing and lets the normal auth flow return 401. SAAS Admins are
 * never tenant-bound and are always allowed through.
 */
class EnsureActiveInstitute
{
    use ResponseTrait;

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?: auth('api')->user();

        if (! $user || $user->user_type === 'SAAS Admin' || ! $user->institute_id) {
            return $next($request);
        }

        // Fresh read — never the cached relation — so deactivating a tenant
        // takes effect on the very next request, not only after re-login.
        $institute = Institute::whereKey($user->institute_id)->first(['id', 'status']);

        if (! $institute || (int) $institute->getRawOriginal('status') !== 1) {
            return $this->responseError(
                [],
                _lang('This institute has been deactivated. Please contact the platform administrator.'),
                403
            );
        }

        if (config('saas.enforce_subscription', false)
            && $institute->subscriptions()->exists()
            && ! $institute->activeSubscription()) {
            return $this->responseError(
                [],
                _lang('Your subscription has expired. Please renew to continue.'),
                403
            );
        }

        return $next($request);
    }
}
