<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class SystemSettingService
{
    private const CACHE_PREFIX = 'system_setting:';

    public function get(string $key, mixed $default = null): mixed
    {
        if (! Schema::hasTable('system_settings')) {
            return $default ?? config("system.{$key}");
        }

        return Cache::remember(self::CACHE_PREFIX.$key, 3600, function () use ($key, $default) {
            $setting = SystemSetting::query()->where('key', $key)->first();

            if (! $setting) {
                return $default;
            }

            if ($setting->is_encrypted) {
                return $setting->getDecryptedValue() ?? $default;
            }

            return $this->castValue($setting->value, $setting->type) ?? $default;
        });
    }

    public function set(string $key, mixed $value, string $type = 'string', string $group = 'general', bool $encrypt = false): void
    {
        $storedValue = $value;

        if ($encrypt && $value !== null) {
            $storedValue = Crypt::encryptString((string) $value);
        } elseif (is_array($value) || is_object($value)) {
            $storedValue = json_encode($value);
            $type = 'json';
        } elseif (is_bool($value)) {
            $storedValue = $value ? '1' : '0';
            $type = 'boolean';
        } else {
            $storedValue = (string) $value;
        }

        SystemSetting::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $storedValue,
                'type' => $type,
                'group' => $group,
                'is_encrypted' => $encrypt,
            ]
        );

        Cache::forget(self::CACHE_PREFIX.$key);
    }

    public function allByGroup(string $group = 'general'): array
    {
        if (! Schema::hasTable('system_settings')) {
            return [];
        }

        return SystemSetting::query()
            ->where('group', $group)
            ->get()
            ->mapWithKeys(function (SystemSetting $setting) {
                $value = $setting->is_encrypted
                    ? $setting->getDecryptedValue()
                    : $this->castValue($setting->value, $setting->type);

                return [$setting->key => $value];
            })
            ->all();
    }

    public function clearCache(?string $key = null): void
    {
        if ($key) {
            Cache::forget(self::CACHE_PREFIX.$key);

            return;
        }

        SystemSetting::query()->pluck('key')->each(fn ($k) => Cache::forget(self::CACHE_PREFIX.$k));
    }

    private function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }
}
