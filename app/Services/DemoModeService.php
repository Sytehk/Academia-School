<?php

namespace App\Services;

use App\Exceptions\DemoModeRestrictedException;
use App\Models\DemoRestriction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class DemoModeService
{
    private const CACHE_KEY = 'demo_restrictions_map';

    public function enabled(): bool
    {
        return (bool) config('system.demo.enabled', false);
    }

    public function autoResetEnabled(): bool
    {
        return $this->enabled() && (bool) config('system.demo.auto_reset', false);
    }

    public function resetIntervalHours(): int
    {
        return (int) config('system.demo.reset_interval', 24);
    }

    public function isRestricted(string $key): bool
    {
        if (! $this->enabled()) {
            return false;
        }

        if (! Schema::hasTable('demo_restrictions')) {
            return $this->isRestrictedByDefault($key);
        }

        $restrictions = $this->getRestrictionsMap();

        if (! array_key_exists($key, $restrictions)) {
            return $this->isRestrictedByDefault($key);
        }

        return (bool) $restrictions[$key];
    }

    public function check(string $key): void
    {
        if ($this->isRestricted($key)) {
            throw new DemoModeRestrictedException($key);
        }
    }

    public function prevent(string $key): void
    {
        $this->check($key);
    }

    public function message(): string
    {
        return (string) config('system.demo.message', 'This action is disabled in demo mode.');
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function getRestrictionsMap(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            return DemoRestriction::query()
                ->pluck('enabled', 'key')
                ->map(fn ($enabled) => (bool) $enabled)
                ->all();
        });
    }

    private function isRestrictedByDefault(string $key): bool
    {
        $defaults = [
            'password_change',
            'email_change',
            'profile_change',
            'system_settings',
            'payment_settings',
            'smtp_settings',
            'api_credentials',
            'database_settings',
            'account_delete',
            'tenant_delete',
            'module_disable',
            'module_delete',
            'data_export',
            'data_import',
            'system_upgrade',
        ];

        return in_array($key, $defaults, true);
    }
}
