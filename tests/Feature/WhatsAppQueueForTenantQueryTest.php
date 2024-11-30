<?php

use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

/**
 * queueForTenant() re-resolved the same WhatsApp setting and event template on
 * every call. The device-sync endpoint resolves ONE WhatsAppService and calls
 * queueForTenant() once per punch in a loop -- for a batch of punches sharing
 * the same tenant and event, that was two provably-repeated queries per call.
 */
it('resolves the setting and template once per tenant/event, not once per message', function () {
    Queue::fake();

    DB::table('whats_app_settings')->updateOrInsert(
        ['institute_id' => 1],
        [
            'branch_id' => null, 'provider' => 'meta', 'status' => 1,
            'phone_number_id' => '123', 'access_token' => 'token',
        ],
    );
    DB::table('whats_app_templates')->updateOrInsert(
        ['institute_id' => 1, 'event' => 'attendance_in'],
        ['name' => 'Attendance In', 'message' => 'Hi {{student_name}}', 'status' => 1],
    );

    $service = app(WhatsAppService::class);

    $queries = 0;
    DB::listen(function ($query) use (&$queries) {
        if (str_contains($query->sql, 'whats_app_settings') || str_contains($query->sql, 'whats_app_templates')) {
            $queries++;
        }
    });

    for ($i = 1; $i <= 20; $i++) {
        $service->queueForTenant(1, 1, '0190000'.$i, 'attendance_in', ['student_name' => "Student {$i}"], $i);
    }

    // One setting query + one template query for the whole batch, not 20 of each.
    expect($queries)->toBeLessThanOrEqual(2);
    expect(DB::table('whats_app_logs')->count())->toBe(20);
});
