<?php

use App\Jobs\SendSmsJob;
use Illuminate\Support\Facades\Queue;
use Modules\Authentication\Models\User;

/**
 * SmsController::send() wrote SmsLog rows directly with new SmsLog()->save(),
 * bypassing SmsService::queueLog() -- the only thing that dispatches
 * SendSmsJob. No job meant these messages depended entirely on the
 * sms:process-logs cron sweeping up status-0 rows, and (separately) the
 * individual-recipient branch read an undefined $user_id left over from a
 * foreach in the OTHER branch.
 */
it('dispatches a SendSmsJob for each bulk recipient', function () {
    Queue::fake();

    $admin = User::factory()->create([
        'institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Admin', 'status' => 1,
    ]);
    $this->actingAs($admin);

    $teacher1 = User::factory()->create(['institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Teacher']);
    $teacher2 = User::factory()->create(['institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Teacher']);

    $response = $this->post(route('sms.compose'), [
        'body' => 'Test broadcast message',
        'user_type' => 'Teacher',
        'users' => [$teacher1->id => '01911110001', $teacher2->id => '01911110002'],
    ]);

    Queue::assertPushed(SendSmsJob::class, 2);
});

it('dispatches a SendSmsJob for an individual recipient without an undefined-variable warning', function () {
    Queue::fake();

    $admin = User::factory()->create([
        'institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Admin', 'status' => 1,
    ]);
    $this->actingAs($admin);

    $response = $this->post(route('sms.compose'), [
        'body' => 'Direct message',
        'individual_number' => '01922220000',
    ]);

    Queue::assertPushed(SendSmsJob::class, 1);
});
