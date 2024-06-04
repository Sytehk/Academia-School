<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Hard subscription enforcement
    |--------------------------------------------------------------------------
    |
    | When true, EnsureActiveInstitute also blocks API access for a tenant
    | whose subscription has expired (status != 'active' or past end_date).
    | An institute with no subscription row at all is still allowed through —
    | that "fail open on no plan" convention is documented in
    | SubscriptionLimitService and lets fresh / trial institutes work.
    |
    | Institute active/deactive (institutes.status) is always enforced,
    | regardless of this flag.
    |
    */
    'enforce_subscription' => (bool) env('SAAS_ENFORCE_SUBSCRIPTION', false),

];
