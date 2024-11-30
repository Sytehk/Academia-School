<?php

use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Modules\Authentication\Models\Branch;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Services\InstituteDefaultSetupService;
use Tests\Support\AcademicFixtures;

/*
|--------------------------------------------------------------------------
| Currency management (Phase 2-5) — catalog CRUD, settings wiring, and the
| format_currency() helper's currency_id/currency_position resolution.
|--------------------------------------------------------------------------
*/

beforeEach(fn () => AcademicFixtures::build());

function currencyPayload(array $overrides = []): array
{
    static $n = 0;
    $n++;
    // 3-letter alpha code, e.g. AAA, AAB, AAC... (the `code` rule requires letters only).
    $letters = str_split(str_pad('', 3, 'A'));
    $letters[2] = chr(ord('A') + ($n % 26));

    return array_merge([
        'name' => "Test Currency {$n}",
        'symbol' => 'X'.$n,
        'code' => implode('', $letters),
        'country_code' => 'XX',
        'status' => 1,
    ], $overrides);
}

// ── CRUD, gated to SAAS Admin ───────────────────────────────────────────

test('a SAAS admin can create, update, and read a currency', function () {
    Passport::actingAs(AcademicFixtures::user('SAAS Admin'), [], 'api');

    $created = $this->postJson('/api/currencies', currencyPayload(['code' => 'ZZZ']))
        ->assertOk()
        ->json('data');

    $this->putJson("/api/currencies/{$created['id']}", currencyPayload(['code' => 'ZZZ', 'name' => 'Renamed']))
        ->assertOk()
        ->assertJsonPath('data.name', 'Renamed');

    $this->getJson("/api/currencies/{$created['id']}")
        ->assertOk()
        ->assertJsonPath('data.code', 'ZZZ');
});

test('a non-SAAS-admin cannot manage currencies', function () {
    Passport::actingAs(AcademicFixtures::user('Admin'), [], 'api');

    $this->postJson('/api/currencies', currencyPayload())->assertStatus(403);
});

test('an unauthenticated request to the currency routes is rejected', function () {
    $this->getJson('/api/currencies')->assertStatus(401);
    $this->postJson('/api/currencies', currencyPayload())->assertStatus(401);
});

test('a duplicate currency code is rejected on create and on update', function () {
    Passport::actingAs(AcademicFixtures::user('SAAS Admin'), [], 'api');

    $this->postJson('/api/currencies', currencyPayload(['code' => 'DUP']))->assertOk();
    $this->postJson('/api/currencies', currencyPayload(['code' => 'DUP']))
        ->assertStatus(422)->assertJsonValidationErrors('code');

    $other = $this->postJson('/api/currencies', currencyPayload(['code' => 'OTH']))->assertOk()->json('data');
    $this->putJson("/api/currencies/{$other['id']}", currencyPayload(['code' => 'DUP']))
        ->assertStatus(422)->assertJsonValidationErrors('code');
});

test('toggling currency status flips between active and inactive', function () {
    Passport::actingAs(AcademicFixtures::user('SAAS Admin'), [], 'api');
    $currency = $this->postJson('/api/currencies', currencyPayload(['status' => 1]))->assertOk()->json('data');

    $this->getJson("/api/currencies-status-update/{$currency['id']}")
        ->assertOk()->assertJsonPath('data.status', 2);

    $this->getJson("/api/currencies-status-update/{$currency['id']}")
        ->assertOk()->assertJsonPath('data.status', 1);
});

test('a currency selected by an institute cannot be deleted, only deactivated', function () {
    Passport::actingAs(AcademicFixtures::user('SAAS Admin'), [], 'api');
    $currency = $this->postJson('/api/currencies', currencyPayload())->assertOk()->json('data');

    DB::table('settings')->updateOrInsert(
        ['institute_id' => 1, 'branch_id' => 1, 'name' => 'currency_id'],
        ['value' => (string) $currency['id']],
    );

    $this->deleteJson("/api/currencies/{$currency['id']}")->assertStatus(409);

    expect(DB::table('currencies')->where('id', $currency['id'])->whereNull('deleted_at')->exists())->toBeTrue();
});

test('a currency not selected by any institute can be deleted', function () {
    Passport::actingAs(AcademicFixtures::user('SAAS Admin'), [], 'api');
    $currency = $this->postJson('/api/currencies', currencyPayload())->assertOk()->json('data');

    $this->deleteJson("/api/currencies/{$currency['id']}")->assertOk();

    expect(DB::table('currencies')->where('id', $currency['id'])->whereNull('deleted_at')->exists())->toBeFalse();
});

// ── format_currency() resolution ────────────────────────────────────────

test('format_currency uses the selected currency catalog entry and left position by default', function () {
    $bdtId = DB::table('currencies')->where('code', 'BDT')->value('id');
    DB::table('settings')->updateOrInsert(
        ['institute_id' => 1, 'branch_id' => 1, 'name' => 'currency_id'],
        ['value' => (string) $bdtId],
    );
    DB::table('settings')->where(['institute_id' => 1, 'branch_id' => 1, 'name' => 'currency_position'])->delete();
    clear_settings_map();

    $this->actingAs(AcademicFixtures::user('Admin'));

    expect(format_currency(1000))->toBe('৳1,000.00');
});

test('format_currency respects a right currency_position', function () {
    $usdId = DB::table('currencies')->where('code', 'USD')->value('id');
    DB::table('settings')->updateOrInsert(['institute_id' => 1, 'branch_id' => 1, 'name' => 'currency_id'], ['value' => (string) $usdId]);
    DB::table('settings')->updateOrInsert(['institute_id' => 1, 'branch_id' => 1, 'name' => 'currency_position'], ['value' => 'right']);
    clear_settings_map();

    $this->actingAs(AcademicFixtures::user('Admin'));

    expect(format_currency(500))->toBe('500.00$');
});

test('format_currency falls back to the legacy currency_symbol when no currency_id is selected', function () {
    DB::table('settings')->where(['institute_id' => 1, 'branch_id' => 1, 'name' => 'currency_id'])->delete();
    DB::table('settings')->updateOrInsert(['institute_id' => 1, 'branch_id' => 1, 'name' => 'currency_symbol'], ['value' => '€']);
    clear_settings_map();

    $this->actingAs(AcademicFixtures::user('Admin'));

    expect(format_currency(10))->toBe('€10.00');
});

test('format_currency falls back to a dollar sign when nothing is configured', function () {
    DB::table('settings')->where('institute_id', 1)->where('branch_id', 1)
        ->whereIn('name', ['currency_id', 'currency_symbol', 'currency_position'])->delete();
    clear_settings_map();

    $this->actingAs(AcademicFixtures::user('Admin'));

    expect(format_currency(10))->toBe('$10.00');
});

// ── convert_amount_to_words() ────────────────────────────────────────────

test('convert_amount_to_words uses the selected currency name', function () {
    $eurId = DB::table('currencies')->where('code', 'EUR')->value('id');
    DB::table('settings')->updateOrInsert(['institute_id' => 1, 'branch_id' => 1, 'name' => 'currency_id'], ['value' => (string) $eurId]);
    clear_settings_map();

    $this->actingAs(AcademicFixtures::user('Admin'));

    expect(convert_amount_to_words(5))->toBe('Five Euro Only');
});

test('convert_amount_to_words falls back to Taka when no currency is selected', function () {
    DB::table('settings')->where(['institute_id' => 1, 'branch_id' => 1, 'name' => 'currency_id'])->delete();
    clear_settings_map();

    $this->actingAs(AcademicFixtures::user('Admin'));

    expect(convert_amount_to_words(5))->toBe('Five Taka Only');
});

// ── Performance: no repeated configuration queries ──────────────────────

test('format_currency does not re-query currency/settings on repeated calls within one request', function () {
    $eurId = DB::table('currencies')->where('code', 'EUR')->value('id');
    DB::table('settings')->updateOrInsert(['institute_id' => 1, 'branch_id' => 1, 'name' => 'currency_id'], ['value' => (string) $eurId]);
    clear_settings_map();

    $this->actingAs(AcademicFixtures::user('Admin'));

    // Warm the caches once (settings_map()'s per-institute memo, plus
    // format_currency()'s and convert_amount_to_words()'s own separate
    // per-currency-id memos) — these calls are allowed to query.
    format_currency(1);
    convert_amount_to_words(1);

    $queryCount = 0;
    DB::listen(function () use (&$queryCount) {
        $queryCount++;
    });

    // Simulates rendering, e.g., 20 invoice line items in one request/response.
    for ($i = 0; $i < 20; $i++) {
        format_currency($i);
        convert_amount_to_words($i);
    }

    expect($queryCount)->toBe(0);
});

// ── Settings API wiring ──────────────────────────────────────────────────

test('updating settings with a currency_id changes what format_currency returns', function () {
    // The WEB settings route stamps both institute_id and branch_id on write
    // (the JSON API route only keys on institute_id — a pre-existing, unrelated
    // gap left untouched here), so it's used to prove the read/write round trip.
    $eurId = DB::table('currencies')->where('code', 'EUR')->value('id');
    $admin = AcademicFixtures::user('Admin');

    $this->actingAs($admin)->post('/administration/general_settings/update', [
        'currency_id' => $eurId,
        'currency_position' => 'left',
    ])->assertRedirect();

    clear_settings_map();
    $this->actingAs($admin);
    expect(format_currency(1))->toBe('€1.00');
});

test('an unknown currency_id is rejected by the settings validation', function () {
    Passport::actingAs(AcademicFixtures::user('Admin'), [], 'api');

    $this->postJson('/api/administration/general_settings', ['currency_id' => 999999])
        ->assertStatus(422)->assertJsonValidationErrors('currency_id');
});

// ── Onboarding default ────────────────────────────────────────────────────

test('a newly provisioned institute is seeded with the configured default currency', function () {
    config(['onboarding.default_currency_code' => 'BDT']);
    $bdtId = DB::table('currencies')->where('code', 'BDT')->value('id');

    $institute = Institute::create(['name' => 'New School', 'domain' => 'new-school.test', 'status' => 1]);
    $branch = Branch::create(['institute_id' => $institute->id, 'name' => 'Main Branch', 'status' => 1]);

    app(InstituteDefaultSetupService::class)->apply($institute, $branch, null);

    $value = DB::table('settings')
        ->where(['institute_id' => $institute->id, 'branch_id' => $branch->id, 'name' => 'currency_id'])
        ->value('value');

    expect((int) $value)->toBe($bdtId);
});
