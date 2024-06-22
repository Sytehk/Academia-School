<?php

use App\Helpers\InstituteHelper;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Authentication\Models\Branch;
use Modules\Authentication\Models\Institute;
use Tests\TestCase;

uses(TestCase::class);

it('resolves the institute and branch from the request host', function () {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ]);

    DB::purge('sqlite');
    DB::setDefaultConnection('sqlite');

    Schema::create('institutes', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email')->nullable();
        $table->string('phone')->nullable();
        $table->string('domain')->nullable();
        $table->boolean('status')->default(true);
        $table->timestamps();
        $table->softDeletes();
    });

    Schema::create('branches', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('institute_id')->constrained('institutes');
        $table->string('name');
        $table->boolean('status')->default(true);
        $table->timestamps();
    });

    $institute = Institute::create([
        'name' => 'Tenant Alpha',
        'email' => 'alpha@example.com',
        'phone' => '1234567890',
        'domain' => 'alpha.example.com',
        'status' => 1,
    ]);

    $branch = Branch::create([
        'institute_id' => $institute->id,
        'name' => 'Main Branch',
        'status' => 1,
    ]);

    $request = Request::create('https://alpha.example.com/app');

    $resolvedInstitute = InstituteHelper::resolveInstituteFromRequest($request);

    expect($resolvedInstitute)->not->toBeNull()
        ->and($resolvedInstitute->id)->toBe($institute->id)
        ->and(InstituteHelper::resolveBranchIdFromRequest($request))->toBe($branch->id);
});
