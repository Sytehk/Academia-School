<?php

use Modules\Academic\Models\Student;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\User;
use Modules\Finance\Models\AttendanceWaiver;
use Modules\Frontend\Models\AdmissionForm;
use Modules\Frontend\Models\Page;
use Modules\SMS\Models\PhoneBook;

/*
|--------------------------------------------------------------------------
| Regression tests for the audit fixes: SMS Phone Book dead resource
| routes, AttendanceWaiver mass-assignment/tenant scoping, and the
| Frontend module's cross-tenant pageShow/AdmissionForm approve+reject
| bugs. See the audit report for root-cause detail on each.
|
| Institute/Page/PhoneBook have no model factories in this codebase, so
| those are seeded with explicit create() calls instead of ::factory().
|--------------------------------------------------------------------------
*/

test('phone-book create route no longer 500s and returns 404', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/phone-book/create')->assertNotFound();
});

test('phone-book show with a non-numeric id returns 404 instead of a type error', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get('/phone-book/not-a-number')->assertNotFound();
});

test('phone-book edit with a real numeric id still renders', function () {
    $user = User::factory()->create();
    $phoneBook = PhoneBook::create([
        'institute_id' => $user->institute_id,
        'branch_id' => $user->branch_id,
        'name' => 'Test Contact',
        'phone' => '01700000000',
        'category_id' => 1,
    ]);

    $this->actingAs($user)->get("/phone-book/{$phoneBook->id}/edit")->assertOk();
});

test('attendance waiver store auto-fills institute_id and branch_id and does not crash', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create([
        'institute_id' => $user->institute_id,
        'branch_id' => $user->branch_id,
    ]);

    $this->actingAs($user)->post('/attendance-waiver', [
        'student_ids' => [$student->id],
        'attendance_fines' => [10],
        'quiz_fines' => [5],
        'lab_fines' => [2],
    ])->assertRedirect();

    $waiver = AttendanceWaiver::where('student_id', $student->id)->first();

    expect($waiver)->not->toBeNull();
    expect($waiver->institute_id)->toBe($user->institute_id);
    expect($waiver->branch_id)->toBe($user->branch_id);
    expect((float) $waiver->total_waiver)->toBe(17.0);
});

test('frontend page api does not leak another institute\'s page by slug', function () {
    Institute::create(['name' => 'Own Tenant', 'domain' => 'own-tenant.test', 'status' => 1]);
    $otherInstitute = Institute::create(['name' => 'Other Tenant', 'domain' => 'other-tenant.test', 'status' => 1]);

    Page::create([
        'institute_id' => $otherInstitute->id,
        'branch_id' => 1,
        'slug' => 'shared-slug',
        'title' => 'Other institute page',
        'author_id' => 1,
    ]);

    $this->withHeaders(['X-Domain' => 'own-tenant.test'])
        ->getJson('/api/frontend/page/shared-slug')
        ->assertNotFound();
});

test('frontend page api returns the correct institute\'s own page', function () {
    $institute = Institute::create(['name' => 'Own Tenant 2', 'domain' => 'own-tenant-2.test', 'status' => 1]);
    $page = Page::create([
        'institute_id' => $institute->id,
        'branch_id' => 1,
        'slug' => 'own-page',
        'title' => 'Own institute page',
        'author_id' => 1,
    ]);

    $this->withHeaders(['X-Domain' => 'own-tenant-2.test'])
        ->getJson('/api/frontend/page/own-page')
        ->assertOk()
        ->assertJsonPath('data.id', $page->id);
});

test('admission form reject cannot process another institute\'s form', function () {
    $user = User::factory()->create();
    $otherInstitute = Institute::create(['name' => 'IDOR Tenant', 'domain' => 'idor-tenant.test', 'status' => 1]);

    $foreignForm = AdmissionForm::create([
        'institute_id' => $otherInstitute->id,
        'class_id' => 1,
        'first_name' => 'CrossTenant',
        'status' => 'Pending',
    ]);

    $this->actingAs($user)->post("/admission-forms/{$foreignForm->id}/reject");

    expect($foreignForm->fresh()->status)->toBe('Pending');
});

test('admission form reject still processes a form belonging to the same institute', function () {
    $user = User::factory()->create();

    $ownForm = AdmissionForm::create([
        'institute_id' => $user->institute_id,
        'class_id' => 1,
        'first_name' => 'OwnTenant',
        'status' => 'Pending',
    ]);

    $this->actingAs($user)->post("/admission-forms/{$ownForm->id}/reject");

    expect($ownForm->fresh()->status)->toBe('Rejected');
});
