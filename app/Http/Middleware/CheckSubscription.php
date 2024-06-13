<?php

namespace App\Http\Middleware;

use App\Traits\ResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hard gate: the acting tenant must have a currently-active, non-expired
 * subscription.
 *
 * This is NOT applied globally. The platform's default posture is fail-open on
 * a missing/expired subscription (see SubscriptionLimitService) so that fresh
 * or trial institutes are never locked out. Attach `check.subscription` to a
 * specific route group only where a hard paywall is wanted.
 *
 * Resource caps (student/branch limits) are a separate concern handled by
 * EnforcePlanLimit on create routes — they are deliberately not checked here.
 */
class CheckSubscription
{
    use ResponseTrait;

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // No tenant context (SAAS Admin / platform action) => nothing to gate.
        if (! $user || $user->user_type === 'SAAS Admin' || ! $user->institute_id) {
            return $next($request);
        }

        $institute = $user->institute;

        if (! $institute || ! $institute->activeSubscription()) {
            return $this->responseError(
                [],
                _lang('Your subscription has expired. Please renew to continue.'),
                403
            );
        }

        return $next($request);
    }
}
