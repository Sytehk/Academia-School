<?php

use Laravel\Passport\Passport;
use Modules\Academic\Models\Notice;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\User;
use Modules\Frontend\Models\AdmissionForm;
use Modules\Frontend\Models\Banner;

/*
|--------------------------------------------------------------------------
| Regression: module API controllers resolved records with a bare
| Model::find($id) (show/update/destroy) with no institute check, so any
| authenticated institute user could read, edit or delete another
| tenant's notices, events, exam schedules, assignments, question-bank
| items, etc. The fix attaches BelongsToTenant / BelongsToInstitute to
| the missed models so every query — find() included — is auto-scoped.
| Notice stands in for the whole class of controllers here.
|--------------------------------------------------------------------------
*/

function tenantAdmin(int $instituteId): void
{
    $user = User::factory()->create([
        'user_type' => 'Admin',
        'institute_id' => $instituteId,
        'branch_id' => 1,
    ]);
    Passport::actingAs($user, [], 'api');
}

function makeNotice(int $instituteId, int $branchId, string $title): Notice
{
    return Notice::withoutGlobalScopes()->forceCreate([
        'institute_id' => $instituteId,
        'branch_id' => $branchId,
        'title' => $title,
        'notice' => 'body',
        'date' => now()->toDateString(),
        'created_by' => 1,
    ]);
}

test('a notice from another institute is invisible and unmodifiable', function () {
    $other = Institute::create(['name' => 'Other N', 'domain' => 'other-n.test', 'platform' => 'WEB', 'status' => 1]);
    $foreign = makeNotice($other->id, 1, 'Foreign notice');

    tenantAdmin(1);

    $this->getJson("/api/notices/{$foreign->id}")->assertStatus(404);
    $this->putJson("/api/notices/{$foreign->id}", ['title' => 'x', 'notice' => 'y'])->assertStatus(404);
    $this->deleteJson("/api/notices/{$foreign->id}")->assertStatus(404);

    expect(Notice::withoutGlobalScopes()->find($foreign->id)->getAttribute('title'))->toBe('Foreign notice');
});

test('the acting institute still sees its own notice', function () {
    tenantAdmin(1);
    $own = makeNotice(1, 1, 'Mine');

    $this->getJson("/api/notices/{$own->id}")->assertOk();
});

/*
| The tenant global scope was rolled out across ~70 more models. Prove at the
| model layer that a cross-tenant row is invisible to find()/all()/update()
| for a representative BelongsToTenant model (Banner) and a
| BelongsToInstitute model (Accounting PaymentHistory has no branch — use
| AccountingLedger which does, plus AdmissionForm which is institute-only).
*/
test('the tenant scope hides cross-institute rows at the model layer', function () {
    $other = Institute::create([
        'name' => 'Scope Other', 'domain' => 'scope-other.test', 'platform' => 'WEB', 'status' => 1,
    ]);

    $foreignBanner = Banner::withoutGlobalScopes()
        ->forceCreate(['institute_id' => $other->id, 'branch_id' => 1]);
    $foreignForm = AdmissionForm::withoutGlobalScopes()
        ->forceCreate(['institute_id' => $other->id, 'class_id' => 1, 'first_name' => 'X', 'status' => 'Pending']);

    tenantAdmin(1);

    expect(Banner::find($foreignBanner->id))->toBeNull();
    expect(Banner::count())->toBe(0);
    expect(AdmissionForm::find($foreignForm->id))->toBeNull();

    // A blind update cannot reach across the tenant boundary.
    Banner::where('id', $foreignBanner->id)->update(['branch_id' => 999]);
    expect((int) Banner::withoutGlobalScopes()->find($foreignBanner->id)->branch_id)->toBe(1);
});
