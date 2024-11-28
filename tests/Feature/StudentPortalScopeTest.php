<?php

use Laravel\Passport\Passport;
use Modules\Academic\Models\Student;
use Modules\Authentication\Models\User;
use Modules\Finance\Models\StudentCollection;

/*
|--------------------------------------------------------------------------
| Phase 2 — Student portal: a student may only ever see their own fee
| collections. `getPaymentInfoDetails($id)` previously ignored the id and
| returned EVERY student_collection in the tenant.
|--------------------------------------------------------------------------
*/

function makeStudentUser(): User
{
    $student = Student::factory()->create(['institute_id' => 1, 'branch_id' => 1]);

    return User::findOrFail($student->user_id);
}

function collectionFor(int $studentId, string $invoice): StudentCollection
{
    return StudentCollection::create([
        'institute_id' => 1, 'branch_id' => 1,
        'student_id' => $studentId, 'class_id' => 1,
        'invoice_id' => $invoice, 'session_id' => 1,
        'total_payable' => 1000, 'total_paid' => 400, 'total_due' => 600,
        'created_by' => 1,
    ]);
}

test('a student only sees their own fee collection details, whatever id is in the URL', function () {
    $userA = makeStudentUser();
    $userB = makeStudentUser();

    collectionFor($userA->student->id, 'INV-A-1');
    collectionFor($userB->student->id, 'INV-B-1');

    $this->actingAs($userA);

    // Ask for student B's id in the URL — must still only get A's rows.
    $response = $this->get('/student/payment-details-info/'.$userB->student->id);

    $response->assertOk();
    $collections = $response->viewData('studentCollections');

    expect($collections)->toHaveCount(1);
    expect($collections->first()->student_id)->toBe($userA->student->id);
    expect($collections->first()->invoice_id)->toBe('INV-A-1');
});

test('student profile payload uses real columns, not the old broken ones', function () {
    $user = makeStudentUser();
    Passport::actingAs($user, [], 'api');

    $res = $this->getJson('/api/student-profile')->assertOk();

    $data = $res->json('data');
    expect($data)->toHaveKeys(['id', 'name', 'email', 'student_id', 'class_id', 'section_id']);
    expect($data['name'])->toBeString()->not->toBe('null null')->not->toBe(' ');
    expect(trim($data['name']))->not->toBe('');
});
