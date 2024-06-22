<?php

use App\Services\Installer\InstallationState;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    InstallationState::resetProgress();
});

it('does not allow skipping ahead to a later step before earlier ones complete', function () {
    expect(InstallationState::canAccess('start'))->toBeTrue()
        ->and(InstallationState::canAccess('requirements'))->toBeFalse()
        ->and(InstallationState::canAccess('database'))->toBeFalse()
        ->and(InstallationState::canAccess('installation'))->toBeFalse();
});

it('unlocks each step only after the previous ones are marked complete', function () {
    InstallationState::markStepComplete('start');
    expect(InstallationState::canAccess('requirements'))->toBeTrue();
    expect(InstallationState::canAccess('permissions'))->toBeFalse();

    InstallationState::markStepComplete('requirements');
    expect(InstallationState::canAccess('permissions'))->toBeTrue();
    expect(InstallationState::canAccess('database'))->toBeFalse();

    InstallationState::markStepComplete('permissions');
    expect(InstallationState::canAccess('database'))->toBeTrue();
    expect(InstallationState::canAccess('installation'))->toBeFalse();

    InstallationState::markStepComplete('database');
    expect(InstallationState::canAccess('installation'))->toBeTrue();
});

it('resets all progress so a fresh attempt cannot inherit a stale completed step', function () {
    InstallationState::markStepComplete('start');
    InstallationState::markStepComplete('requirements');
    InstallationState::resetProgress();

    expect(InstallationState::canAccess('requirements'))->toBeFalse();
});
