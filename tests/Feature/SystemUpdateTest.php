<?php

use App\Exceptions\SystemUpdateException;
use App\Models\SystemSetting;
use App\Services\SystemSettingService;
use App\Services\SystemUpdate\UpdateLockManager;
use App\Services\SystemUpdate\UpdatePackageValidator;
use App\Services\SystemUpdate\UpdateVersionManager;
use Database\Seeders\DemoRestrictionSeeder;
use Database\Seeders\SystemSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(SystemSettingSeeder::class);
    $this->seed(DemoRestrictionSeeder::class);
});

function createTestUpdateZip(string $dir, array $metadata, array $files = []): string
{
    $zipPath = $dir.'/update.zip';
    $zip = new ZipArchive;
    $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString('update.json', json_encode($metadata, JSON_PRETTY_PRINT));

    foreach ($files as $path => $content) {
        $zip->addFromString('files/'.$path, $content);
    }

    $zip->close();

    return $zipPath;
}

test('valid update package passes validation', function () {
    $dir = storage_path('app/testing-updates/'.uniqid());
    File::ensureDirectoryExists($dir);

    SystemSetting::query()->updateOrCreate(['key' => 'app_version'], ['value' => '1.0.0', 'type' => 'string', 'group' => 'version']);
    app(SystemSettingService::class)->clearCache();

    $zip = createTestUpdateZip($dir, [
        'name' => 'Test Update',
        'version' => '1.1.0',
        'minimum_version' => '1.0.0',
        'requires_php' => '>=8.2',
        'requires_laravel' => '>=11.0',
        'run_migrations' => false,
        'run_commands' => false,
        'clear_cache' => false,
    ], [
        'app/test.txt' => 'hello',
    ]);

    $result = app(UpdatePackageValidator::class)->validate($zip);

    expect($result['ready'])->toBeTrue()
        ->and($result['metadata']['version'])->toBe('1.1.0');

    File::deleteDirectory($dir);
});

test('downgrade attempt fails validation', function () {
    $dir = storage_path('app/testing-updates/'.uniqid());
    File::ensureDirectoryExists($dir);

    SystemSetting::query()->updateOrCreate(['key' => 'app_version'], ['value' => '2.0.0', 'type' => 'string', 'group' => 'version']);
    app(SystemSettingService::class)->clearCache();

    $zip = createTestUpdateZip($dir, [
        'version' => '1.5.0',
        'minimum_version' => '1.0.0',
    ]);

    $result = app(UpdatePackageValidator::class)->validate($zip);

    expect($result['ready'])->toBeFalse()
        ->and($result['checks']['upgrade_direction']['passed'])->toBeFalse();

    File::deleteDirectory($dir);
});

test('zip slip paths are rejected', function () {
    $validator = app(UpdatePackageValidator::class);

    expect($validator->isUnsafePath('../.env'))->toBeTrue();
    expect($validator->isUnsafePath('/etc/passwd'))->toBeTrue();
    expect($validator->isUnsafePath('app/test.php'))->toBeFalse();
});

test('incompatible minimum version fails', function () {
    SystemSetting::query()->updateOrCreate(['key' => 'app_version'], ['value' => '1.0.0', 'type' => 'string', 'group' => 'version']);
    app(SystemSettingService::class)->clearCache();

    $checks = app(UpdateVersionManager::class)->validateUpgrade([
        'version' => '2.0.0',
        'minimum_version' => '1.5.0',
    ]);

    expect($checks['minimum_version']['passed'])->toBeFalse();
});

test('disallowed commands are rejected by command runner config', function () {
    $allowed = config('system.updates.allowed_commands');

    expect($allowed)->toContain('migrate --force')
        ->and($allowed)->not->toContain('db:wipe');
});

test('update lock prevents duplicate updates', function () {
    $lock = app(UpdateLockManager::class);

    $lock->acquire(60);
    expect($lock->isLocked())->toBeTrue();

    expect(fn () => $lock->acquire(60))
        ->toThrow(SystemUpdateException::class);

    $lock->release();
    expect($lock->isLocked())->toBeFalse();
});
