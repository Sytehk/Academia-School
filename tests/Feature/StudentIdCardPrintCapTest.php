<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Authentication\Models\User;

/**
 * studentIdCardList() passed a hard-coded 200 as $perPage to
 * getStudentsByClassSectionGroup(), which paginate()s whenever perPage > 0. A
 * class of 210+ silently printed only the first 200 cards with no pagination
 * control on the print page and no indication that anyone was missing.
 */
it('prints an ID card for every student in a class larger than the old 200-row cap', function () {
    $login = User::factory()->create([
        'institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Admin', 'status' => 1,
    ]);
    $this->actingAs($login);

    DB::table('academic_years')->updateOrInsert(['id' => 1], ['session' => '2026', 'year' => '2026']);
    DB::table('classes')->updateOrInsert(
        ['id' => 1],
        ['institute_id' => 1, 'branch_id' => 1, 'class_name' => 'One'],
    );
    DB::table('sections')->updateOrInsert(
        ['id' => 1],
        ['institute_id' => 1, 'branch_id' => 1, 'class_id' => 1, 'section_name' => 'A'],
    );

    $total = 210;
    $rows = [];
    for ($i = 1; $i <= $total; $i++) {
        $userId = 5000 + $i;
        DB::table('users')->updateOrInsert(
            ['id' => $userId],
            [
                'institute_id' => 1, 'branch_id' => 1, 'name' => "Card Student {$i}",
                'phone' => '0198'.str_pad((string) $i, 7, '0', STR_PAD_LEFT),
                'email' => "card{$i}@test.local", 'password' => Hash::make('12345678'),
                'user_type' => 'Student', 'status' => 1,
            ],
        );
        DB::table('students')->updateOrInsert(
            ['id' => $userId],
            [
                'institute_id' => 1, 'branch_id' => 1, 'user_id' => $userId,
                'first_name' => "Card{$i}", 'phone' => '0198'.str_pad((string) $i, 7, '0', STR_PAD_LEFT),
            ],
        );
        $rows[] = [
            'id' => $userId,
            'institute_id' => 1, 'branch_id' => 1, 'session_id' => 1, 'student_id' => $userId,
            'class_id' => 1, 'section_id' => 1, 'roll' => (string) $i,
        ];
    }
    foreach (array_chunk($rows, 50) as $chunk) {
        DB::table('student_sessions')->upsert($chunk, ['id']);
    }
    DB::table('settings')->updateOrInsert(
        ['institute_id' => 1, 'branch_id' => 1, 'name' => 'academic_year'],
        ['value' => '1'],
    );

    $encoded = base64_encode(json_encode([
        'class_id' => 1, 'section_id' => 1, 'group_id' => 0, 'validity' => '2027-01-01',
    ]));

    $response = $this->get(route('student.id.card.print', $encoded));

    $response->assertOk();

    // `roll` is sorted as a VARCHAR, so which specific students would fall past
    // a 200-row cap depends on lexicographic order ("205" can sort before
    // position 200) — asserting on a couple of specific rolls would not reliably
    // catch the cap. Count the actual rendered card divs instead: the view wraps
    // each student in exactly one <div class="idc-pair">, giving a direct,
    // sort-order-independent measure of how many students were printed. Match
    // the opening div tag specifically — the class name also appears 3 times in
    // the page's own <style> block, which a bare substring count would include.
    $cardCount = substr_count($response->getContent(), 'div class="idc-pair"');

    expect($cardCount)->toBe($total);
});
