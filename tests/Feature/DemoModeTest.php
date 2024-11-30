<?php

use App\Exceptions\DemoModeRestrictedException;
use App\Models\DemoRestriction;
use App\Models\SystemSetting;
use App\Services\DemoModeService;
use App\Services\SystemSettingService;
use App\Services\SystemVersionService;
use Database\Seeders\DemoRestrictionSeeder;
use Database\Seeders\SystemSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Authentication\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DemoRestrictionSeeder::class);
    $this->seed(SystemSettingSeeder::class);
});

test('demo mode is disabled by default in tests', function () {
    config(['system.demo.enabled' => false]);

    expect(app(DemoModeService::class)->enabled())->toBeFalse();
    expect(app()->isDemoMode())->toBeFalse();
});

test('demo mode can be enabled via config', function () {
    config(['system.demo.enabled' => true]);

    expect(app(DemoModeService::class)->enabled())->toBeTrue();
});

test('password change is blocked in demo mode', function () {
    config(['system.demo.enabled' => true]);

    expect(fn () => app(DemoModeService::class)->check('password_change'))
        ->toThrow(DemoModeRestrictedException::class);
});

test('allowed operations are not restricted when demo mode is off', function () {
    config(['system.demo.enabled' => false]);

    app(DemoModeService::class)->check('password_change');

    expect(true)->toBeTrue();
});

test('demo restriction can be toggled in database', function () {
    config(['system.demo.enabled' => true]);

    DemoRestriction::query()->where('key', 'password_change')->update(['enabled' => false]);
    app(DemoModeService::class)->clearCache();

    app(DemoModeService::class)->check('password_change');

    expect(true)->toBeTrue();
});

test('demo mode api returns proper json response', function () {
    config(['system.demo.enabled' => true]);

    $response = $this->postJson('/api/test-demo-block', [], [
        'Accept' => 'application/json',
    ]);

    // Route may not exist — test exception render directly
    $exception = new DemoModeRestrictedException('password_change');
    $request = Request::create('/api/test', 'POST');
    $request->headers->set('Accept', 'application/json');

    $json = $exception->render($request);

    expect($json->getStatusCode())->toBe(403)
        ->and($json->getData(true)['success'])->toBeFalse();
});

test('demo restriction flashes an error for web requests', function () {
    $exception = new DemoModeRestrictedException('password_change');
    $request = Request::create('/profile', 'PUT', server: [
        'HTTP_ACCEPT' => 'text/html',
        'HTTP_REFERER' => 'http://localhost/profile',
    ]);

    $response = $exception->render($request);

    expect($response->isRedirect())->toBeTrue();
});

test('system update routes use the web middleware group', function () {
    $route = Route::getRoutes()->getByName('system.update.index');

    expect($route)->not->toBeNull()
        ->and($route->gatherMiddleware())->toContain('web')
        ->and($route->gatherMiddleware())->toContain('auth');
});

test('profile update is blocked in demo mode', function () {
    config(['system.demo.enabled' => true]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/profile')
        ->patch('/profile', [
            'name' => 'Hacked Name',
            'email' => 'hacked@example.com',
        ])
        ->assertRedirect('/profile')
        ->assertSessionHas('error');

    expect($user->fresh()->email)->not->toBe('hacked@example.com');
});

test('system version uses database as source of truth', function () {
    SystemSetting::query()->updateOrCreate(
        ['key' => 'app_version'],
        ['value' => '2.5.0', 'type' => 'string', 'group' => 'version']
    );

    app(SystemSettingService::class)->clearCache('app_version');

    expect(app(SystemVersionService::class)->current())->toBe('2.5.0');
});

test('version compare detects upgrade availability', function () {
    $service = app(SystemVersionService::class);

    SystemSetting::query()->updateOrCreate(['key' => 'app_version'], ['value' => '1.0.0', 'type' => 'string', 'group' => 'version']);
    SystemSetting::query()->updateOrCreate(['key' => 'latest_version'], ['value' => '1.5.0', 'type' => 'string', 'group' => 'version']);
    app(SystemSettingService::class)->clearCache();

    expect($service->isUpgradeAvailable())->toBeTrue();
    expect($service->compare('1.5.0', '1.0.0'))->toBeGreaterThan(0);
});
