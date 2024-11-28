<?php

use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Modules\Academic\Models\Student;
use Modules\Authentication\Models\User;

/*
|--------------------------------------------------------------------------
| Phase 2 — portal authorization layer.
|
| Teacher / Student / Parent accounts get an ordinary Passport token from the
| shared /api/login. RejectPortalApiUser must keep that token out of every
| back-office API endpoint while leaving the dedicated portal routes reachable.
|--------------------------------------------------------------------------
*/

function portalUser(string $type): User
{
    return User::factory()->create([
        'user_type' => $type,
        'institute_id' => 1,
        'branch_id' => 1,
        'status' => 1,
    ]);
}

dataset('portal types', ['Student', 'Teacher', 'Parent']);

test('a portal token cannot reach the admin student API', function (string $type) {
    Passport::actingAs(portalUser($type), [], 'api');

    $this->getJson('/api/students')->assertStatus(403);
    $this->postJson('/api/students', [])->assertStatus(403);
    $this->deleteJson('/api/students/1')->assertStatus(403);
})->with('portal types');

test('a portal token cannot reach the admin teacher / staff API', function (string $type) {
    Passport::actingAs(portalUser($type), [], 'api');

    $this->getJson('/api/teachers')->assertStatus(403);
    $this->getJson('/api/staffs')->assertStatus(403);
})->with('portal types');

test('a portal token cannot reach the finance / fee-collection API', function (string $type) {
    Passport::actingAs(portalUser($type), [], 'api');

    $this->getJson('/api/fees')->assertStatus(403);
    $this->postJson('/api/quick-collection', [])->assertStatus(403);
    $this->postJson('/api/waivers', [])->assertStatus(403);
})->with('portal types');

test('a portal token cannot reach the payment-gateway config API', function (string $type) {
    Passport::actingAs(portalUser($type), [], 'api');

    $this->getJson('/api/digital-payment')->assertStatus(403);
    $this->putJson('/api/digital-payment/1', [])->assertStatus(403);
})->with('portal types');

test('a portal token cannot post attendance through the admin API', function (string $type) {
    Passport::actingAs(portalUser($type), [], 'api');

    $this->postJson('/api/student-attendance', [])->assertStatus(403);
})->with('portal types');

test('a non-parent token cannot reach the parent CRUD API', function () {
    Passport::actingAs(portalUser('Student'), [], 'api');

    $this->getJson('/api/parents')->assertStatus(403);
    $this->putJson('/api/parents/1', ['name' => 'x'])->assertStatus(403);
    $this->deleteJson('/api/parents/1')->assertStatus(403);
});

test('a back-office admin token still reaches the admin API', function () {
    $admin = User::factory()->create([
        'user_type' => 'Admin', 'institute_id' => 1, 'branch_id' => 1, 'status' => 1,
    ]);
    Passport::actingAs($admin, [], 'api');

    // Not 403 — reaches the controller (200, or a validation/empty response).
    $this->getJson('/api/students')->assertStatus(200);
});

test('the student portal still reaches its own endpoints', function () {
    $student = Student::factory()->create();
    Passport::actingAs(User::findOrFail($student->user_id), [], 'api');

    // Own portal route — carries the `student` middleware, so RejectPortalApiUser
    // lets it through. Never 403.
    $this->getJson('/api/student/my-profile')->assertOk();
});

test('an inactive account is refused a web session', function () {
    $user = User::factory()->create([
        'user_type' => 'Admin', 'institute_id' => 1, 'branch_id' => 1,
        'status' => 0,
        'password' => Hash::make('secret-pass'),
    ]);

    $this->from('/login')
        ->post('/login', ['email' => $user->email, 'password' => 'secret-pass'])
        ->assertRedirect('/login')
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('an inactive portal token is rejected by the portal middleware', function () {
    $student = Student::factory()->create();
    User::whereKey($student->user_id)->update(['status' => 0]);
    Passport::actingAs(User::findOrFail($student->user_id), [], 'api');

    $this->getJson('/api/student/my-profile')->assertStatus(403);
});
