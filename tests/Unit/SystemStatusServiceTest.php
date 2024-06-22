<?php

use App\Services\SystemStatusService;
use App\Services\SystemVersionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('reports no pending migrations once the database is fully migrated', function () {
    $status = new SystemStatusService(app(SystemVersionService::class));

    expect($status->hasPendingMigrations())->toBeFalse();
});

it('flags attention as needed when an update is available even if everything else is fine', function () {
    $versionService = Mockery::mock(SystemVersionService::class);
    $versionService->shouldReceive('current')->andReturn('1.0.0');
    $versionService->shouldReceive('latest')->andReturn('1.1.0');
    $versionService->shouldReceive('isUpgradeAvailable')->andReturn(true);

    $status = new SystemStatusService($versionService);

    expect($status->updateAvailable())->toBeTrue()
        ->and($status->needsAttention())->toBeTrue();
});

it('does not need attention when up to date, migrated and linked', function () {
    $versionService = Mockery::mock(SystemVersionService::class);
    $versionService->shouldReceive('isUpgradeAvailable')->andReturn(false);

    $status = Mockery::mock(SystemStatusService::class, [$versionService])->makePartial();
    $status->shouldReceive('hasPendingMigrations')->andReturn(false);
    $status->shouldReceive('storageLinked')->andReturn(true);

    expect($status->needsAttention())->toBeFalse();
});
