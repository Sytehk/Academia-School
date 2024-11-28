<?php

use Laravel\Passport\Passport;
use Modules\Authentication\Models\User;

/*
|--------------------------------------------------------------------------
| Regression: the platform-administration API (institute CRUD, plans,
| onboarding, SaaS CMS/settings) must reject a valid token that does not
| belong to a SAAS Admin. Before the fix every institute user — each of
| whom is issued a Passport token at /api/login — could list and mutate
| every tenant on the platform.
|--------------------------------------------------------------------------
*/

test('an institute admin cannot list institutes through the platform API', function () {
    $user = User::factory()->create(['user_type' => 'Admin']);
    Passport::actingAs($user, [], 'api');

    $this->getJson('/api/institutes')->assertStatus(403);
});

test('an institute admin cannot toggle an institute status', function () {
    $user = User::factory()->create(['user_type' => 'Admin']);
    Passport::actingAs($user, [], 'api');

    $this->getJson('/api/institutes-status-update/1')->assertStatus(403);
});

test('an institute admin cannot mutate platform plans', function () {
    $user = User::factory()->create(['user_type' => 'Admin']);
    Passport::actingAs($user, [], 'api');

    $this->postJson('/api/plans', ['name' => 'Hacked', 'price' => 0])->assertStatus(403);
});

test('a saas admin can list institutes through the platform API', function () {
    $user = User::factory()->create(['user_type' => 'SAAS Admin']);
    Passport::actingAs($user, [], 'api');

    $this->getJson('/api/institutes')->assertOk();
});
