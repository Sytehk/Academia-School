<?php

use App\Models\SystemModule;
use App\Services\ModuleManagerService;
use Database\Seeders\DemoRestrictionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DemoRestrictionSeeder::class);
});

test('module can be disabled and enabled', function () {
    SystemModule::query()->create([
        'name' => 'TestModule',
        'slug' => 'testmodule',
        'version' => '1.0.0',
        'enabled' => true,
        'status' => 'active',
        'dependencies' => [],
    ]);

    $service = app(ModuleManagerService::class);

    expect($service->isEnabled('testmodule'))->toBeTrue();

    $service->disable('testmodule', skipDemoCheck: true);

    expect(SystemModule::query()->where('slug', 'testmodule')->value('enabled'))->toBeFalse();
});

test('module with dependents cannot be disabled', function () {
    SystemModule::query()->create([
        'name' => 'CoreDep',
        'slug' => 'coredep',
        'version' => '1.0.0',
        'enabled' => true,
        'dependencies' => [],
    ]);

    SystemModule::query()->create([
        'name' => 'Dependent',
        'slug' => 'dependent',
        'version' => '1.0.0',
        'enabled' => true,
        'dependencies' => ['CoreDep'],
    ]);

    $service = app(ModuleManagerService::class);

    expect(fn () => $service->disable('coredep', skipDemoCheck: true))
        ->toThrow(RuntimeException::class);
});

test('disabled module returns false from isEnabled', function () {
    SystemModule::query()->create([
        'name' => 'OffModule',
        'slug' => 'offmodule',
        'version' => '1.0.0',
        'enabled' => false,
        'status' => 'disabled',
    ]);

    expect(app(ModuleManagerService::class)->isEnabled('offmodule'))->toBeFalse();
});
