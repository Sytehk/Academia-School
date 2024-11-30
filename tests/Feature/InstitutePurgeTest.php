<?php

use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\Plan;
use Modules\Authentication\Models\SAASSetting;
use Modules\Authentication\Models\Subscription;
use Modules\Authentication\Models\User;

/*
|--------------------------------------------------------------------------
| DELETE /api/institutes/{id} permanently wipes the institute and every row
| attached to it (InstitutePurgeService), so the unique email/domain frees up
| and the same school can be registered again. SAAS Admin only.
|--------------------------------------------------------------------------
*/

function actingAsSaasAdmin(): void
{
    $admin = User::factory()->create(['user_type' => 'SAAS Admin', 'institute_id' => null]);
    Passport::actingAs($admin, [], 'api');
}

function makeTenant(string $domain = 'purge.test', string $email = 'purge@school.test'): array
{
    $institute = Institute::create([
        'name' => 'Purge Co', 'email' => $email, 'domain' => $domain,
        'platform' => 'WEB', 'status' => 1,
    ]);

    $branchId = DB::table('branches')->insertGetId([
        'institute_id' => $institute->id, 'name' => 'Main', 'status' => 1,
    ]);

    $user = User::factory()->create([
        'user_type' => 'Admin', 'institute_id' => $institute->id, 'branch_id' => $branchId,
    ]);

    $plan = Plan::query()->firstOr(fn () => Plan::create([
        'name' => 'Purge Plan', 'price' => 0, 'duration_days' => 365,
        'student_limit' => 50, 'branch_limit' => 1,
    ]));

    $subscription = Subscription::create([
        'institute_id' => $institute->id, 'plan_id' => $plan->id, 'status' => 'active',
        'start_date' => now(), 'end_date' => now()->addYear(),
    ]);

    DB::table('subscription_items')->insert([
        'subscription_id' => $subscription->id, 'reference_no' => 'PAY-X', 'amount_paid' => 10,
        'payment_method' => 'Manual',
    ]);

    DB::table('domain_requests')->insert([
        'institute_id' => $institute->id, 'requested_domain' => 'custom.test', 'status' => 'pending',
    ]);

    return compact('institute', 'branchId', 'user', 'subscription');
}

test('deleting an institute wipes every related row', function () {
    actingAsSaasAdmin();
    ['institute' => $institute, 'branchId' => $branchId, 'user' => $user, 'subscription' => $subscription] = makeTenant();

    $this->deleteJson("/api/institutes/{$institute->id}")->assertOk();

    expect(Institute::withTrashed()->find($institute->id))->toBeNull();
    expect(DB::table('branches')->where('id', $branchId)->exists())->toBeFalse();
    expect(DB::table('users')->where('id', $user->id)->exists())->toBeFalse();
    expect(DB::table('subscriptions')->where('id', $subscription->id)->exists())->toBeFalse();
    expect(DB::table('subscription_items')->where('subscription_id', $subscription->id)->exists())->toBeFalse();
    expect(DB::table('domain_requests')->where('institute_id', $institute->id)->exists())->toBeFalse();
    expect(DB::table('model_has_roles')->where('model_id', $user->id)->exists())->toBeFalse();
});

test('the email and domain free up for re-registration', function () {
    actingAsSaasAdmin();
    ['institute' => $institute] = makeTenant('again.test', 'again@school.test');

    $this->deleteJson("/api/institutes/{$institute->id}")->assertOk();

    $fresh = Institute::create([
        'name' => 'Again Co', 'email' => 'again@school.test', 'domain' => 'again.test',
        'platform' => 'WEB', 'status' => 1,
    ]);

    expect($fresh->exists)->toBeTrue();
});

test('an already soft-deleted institute can still be purged', function () {
    actingAsSaasAdmin();
    ['institute' => $institute] = makeTenant('soft.test', 'soft@school.test');
    $institute->delete();

    $this->deleteJson("/api/institutes/{$institute->id}")->assertOk();

    expect(Institute::withTrashed()->find($institute->id))->toBeNull();
});

test('the configured default template institute is protected', function () {
    actingAsSaasAdmin();
    ['institute' => $institute] = makeTenant('tmpl.test', 'tmpl@school.test');

    SAASSetting::query()->updateOrCreate(
        ['name' => 'default_template_institute_id'],
        ['value' => (string) $institute->id],
    );

    $this->deleteJson("/api/institutes/{$institute->id}")->assertStatus(409);

    expect(Institute::withTrashed()->find($institute->id))->not->toBeNull();
});

test('a non SAAS admin cannot delete an institute', function () {
    $user = User::factory()->create(['user_type' => 'Admin', 'institute_id' => 1, 'branch_id' => 1]);
    Passport::actingAs($user, [], 'api');

    $this->deleteJson('/api/institutes/1')->assertStatus(403);
});
