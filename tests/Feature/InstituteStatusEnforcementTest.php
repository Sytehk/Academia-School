<?php

use Laravel\Passport\Passport;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\Plan;
use Modules\Authentication\Models\Subscription;
use Modules\Authentication\Models\User;

/*
|--------------------------------------------------------------------------
| A tenant whose institute has been deactivated (institutes.status != 1)
| must not be able to authenticate or use the API. SAAS Admins and public
| endpoints are unaffected. Enforced by EnsureActiveInstitute (api group)
| plus a check in AuthController@login.
|--------------------------------------------------------------------------
*/

test('a user of a deactivated institute cannot log in', function () {
    $inst = Institute::create(['name' => 'Down Co', 'domain' => 'down.test', 'platform' => 'WEB', 'status' => 0]);
    $user = User::factory()->create(['user_type' => 'Admin', 'institute_id' => $inst->id]);
    // factory hashes '12345678'

    $this->postJson('/api/login', ['email' => $user->email, 'password' => '12345678'])
        ->assertStatus(403);
});

test('an existing token stops working once the institute is deactivated', function () {
    $inst = Institute::create(['name' => 'Toggling Co', 'domain' => 'toggle.test', 'platform' => 'WEB', 'status' => 1]);
    $user = User::factory()->create(['user_type' => 'Admin', 'institute_id' => $inst->id, 'branch_id' => 1]);
    Passport::actingAs($user, [], 'api');

    $this->getJson('/api/notices')->assertOk();

    $inst->update(['status' => 0]);

    $this->getJson('/api/notices')->assertStatus(403);
});

test('a SAAS admin is unaffected by any institute status', function () {
    $saas = User::factory()->create(['user_type' => 'SAAS Admin', 'institute_id' => null]);
    Passport::actingAs($saas, [], 'api');

    $this->getJson('/api/institutes')->assertOk();
});

test('the baseline active institute keeps working', function () {
    $user = User::factory()->create(['user_type' => 'Admin', 'institute_id' => 1, 'branch_id' => 1]);
    Passport::actingAs($user, [], 'api');

    $this->getJson('/api/notices')->assertOk();
});

test('with SAAS_ENFORCE_SUBSCRIPTION on, an expired subscription is blocked but a missing one is not', function () {
    config(['saas.enforce_subscription' => true]);

    $plan = Plan::create([
        'name' => 'Test Plan', 'price' => 0, 'duration_days' => 30, 'student_limit' => 50, 'branch_limit' => 1,
    ]);

    // institute with an EXPIRED subscription -> blocked
    $expired = Institute::create(['name' => 'Expired Co', 'domain' => 'exp.test', 'platform' => 'WEB', 'status' => 1]);
    Subscription::create([
        'institute_id' => $expired->id, 'plan_id' => $plan->id, 'status' => 'active',
        'start_date' => now()->subYear(), 'end_date' => now()->subDay(),
    ]);
    $u1 = User::factory()->create(['user_type' => 'Admin', 'institute_id' => $expired->id, 'branch_id' => 1]);
    Passport::actingAs($u1, [], 'api');
    $this->getJson('/api/notices')->assertStatus(403);

    // institute with NO subscription at all -> allowed (documented fail-open)
    $fresh = Institute::create(['name' => 'Fresh Co', 'domain' => 'fresh.test', 'platform' => 'WEB', 'status' => 1]);
    $u2 = User::factory()->create(['user_type' => 'Admin', 'institute_id' => $fresh->id, 'branch_id' => 1]);
    Passport::actingAs($u2, [], 'api');
    $this->getJson('/api/notices')->assertOk();
});
