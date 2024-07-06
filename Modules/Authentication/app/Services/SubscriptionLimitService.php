<?php

namespace Modules\Authentication\Services;

use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\Plan;

/**
 * Single source of truth for package/plan resource limits.
 *
 * Every limit is read from the institute's active subscription plan — nothing is
 * hardcoded. Adding a new limited resource is a one-line change to {@see registry()}
 * (plus the matching plan column), after which the middleware, helpers and UI all
 * enforce it automatically.
 *
 * "Unlimited" convention: a plan limit that is null or <= 0 means unlimited. This lets
 * an "Unlimited/Enterprise" plan opt out of a cap without a magic number, while existing
 * positive limits keep enforcing exactly as before.
 *
 * Fail-open on no plan: if an institute has no active subscription we do NOT block
 * creation here — subscription *presence* is a separate policy. This service only
 * enforces the numeric caps of a plan that actually exists, so installs without
 * subscriptions (e.g. fresh/demo) are never accidentally locked out.
 */
class SubscriptionLimitService
{
    /**
     * Resource key => how to read its plan limit and count current usage.
     *
     * @return array<string, array{limit_column: string, label: string, usage: callable(Institute): int}>
     */
    protected function registry(): array
    {
        return [
            'branch' => [
                'limit_column' => 'branch_limit',
                'label' => 'branches',
                'usage' => fn (Institute $institute): int => $institute->branches()->count(),
            ],
            'student' => [
                'limit_column' => 'student_limit',
                'label' => 'students',
                'usage' => fn (Institute $institute): int => $institute->students()->count(),
            ],
            // Extend here as plan columns are added, e.g.:
            // 'teacher' => ['limit_column' => 'teacher_limit', 'label' => 'teachers',
            //     'usage' => fn (Institute $i) => $i->teachers()->count()],
        ];
    }

    public function supports(string $resource): bool
    {
        return array_key_exists($resource, $this->registry());
    }

    public function label(string $resource): string
    {
        return $this->registry()[$resource]['label'] ?? $resource;
    }

    /**
     * The full active plan for an institute, or null when there is no active subscription.
     *
     * Note: we load the Plan directly rather than through Subscription::plan(), because
     * that relation intentionally selects only id+name and would leave the limit columns
     * null.
     */
    public function plan(Institute $institute): ?Plan
    {
        $subscription = $institute->activeSubscription();
        if (! $subscription) {
            return null;
        }

        return Plan::find($subscription->plan_id);
    }

    /**
     * The numeric limit for a resource, or null when unlimited / not applicable.
     */
    public function limitFor(Institute $institute, string $resource): ?int
    {
        if (! $this->supports($resource)) {
            return null;
        }

        $plan = $this->plan($institute);
        if (! $plan) {
            return null; // fail-open: no active plan => no cap enforced here
        }

        $limit = $plan->{$this->registry()[$resource]['limit_column']};

        // null or <= 0 means "unlimited".
        if ($limit === null || (int) $limit <= 0) {
            return null;
        }

        return (int) $limit;
    }

    /**
     * Current usage count for a resource within the institute.
     */
    public function usage(Institute $institute, string $resource): int
    {
        if (! $this->supports($resource)) {
            return 0;
        }

        return (int) ($this->registry()[$resource]['usage'])($institute);
    }

    /**
     * Remaining allowance, or null when unlimited.
     */
    public function remaining(Institute $institute, string $resource): ?int
    {
        $limit = $this->limitFor($institute, $resource);
        if ($limit === null) {
            return null;
        }

        return max(0, $limit - $this->usage($institute, $resource));
    }

    /**
     * Whether the institute may create $count more of the resource.
     */
    public function canCreate(Institute $institute, string $resource, int $count = 1): bool
    {
        $limit = $this->limitFor($institute, $resource);
        if ($limit === null) {
            return true; // unlimited or no active plan
        }

        return ($this->usage($institute, $resource) + $count) <= $limit;
    }

    /**
     * A human-readable "limit reached" message for a resource.
     */
    public function limitReachedMessage(Institute $institute, string $resource): string
    {
        $limit = $this->limitFor($institute, $resource);

        return _lang('You have reached your plan limit of').' '.$limit.' '.$this->label($resource).'. '
            ._lang('Please upgrade your plan to add more.');
    }
}
