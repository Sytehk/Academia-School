<?php

namespace App\Http\Middleware;

use App\Traits\ResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Modules\Authentication\Services\SubscriptionLimitService;
use RealRashid\SweetAlert\Facades\Alert;

/**
 * Enforces a package/plan resource limit on creation requests.
 *
 * Usage: apply `plan.limit:branch` (or any registered resource key) to a route or
 * resource group. It only acts on POST (create) requests, so it is safe to attach to a
 * whole resource — index/show/edit (GET) and update/delete pass straight through, only
 * store is guarded.
 *
 * All numbers come from {@see SubscriptionLimitService} (the plan), never hardcoded.
 */
class EnforcePlanLimit
{
    use ResponseTrait;

    public function __construct(private readonly SubscriptionLimitService $limits) {}

    public function handle(Request $request, Closure $next, string $resource): mixed
    {
        // Only creation is capped; reading/updating/deleting is always allowed.
        if (! $request->isMethod('post')) {
            return $next($request);
        }

        $institute = $request->user()?->institute;

        // No institute context (e.g. super-admin/platform actions) => nothing to cap.
        if (! $institute) {
            return $next($request);
        }

        if ($this->limits->canCreate($institute, $resource)) {
            return $next($request);
        }

        $message = $this->limits->limitReachedMessage($institute, $resource);

        if ($request->expectsJson()) {
            return $this->responseError([], $message, 403);
        }

        Alert::error(_lang('Limit reached'), $message);

        return redirect()->back()->withInput();
    }
}
