<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SystemVersionService
{
    public function __construct(
        private readonly SystemSettingService $settings,
    ) {}

    public function current(): string
    {
        $dbVersion = $this->settings->get('app_version');

        if ($dbVersion) {
            return (string) $dbVersion;
        }

        if (defined('SOFTWARE_VERSION')) {
            return (string) SOFTWARE_VERSION;
        }

        return (string) config('system.version', '1.0.0');
    }

    public function latest(): ?string
    {
        return $this->settings->get('latest_version');
    }

    public function compare(string $versionA, string $versionB): int
    {
        return version_compare($this->normalize($versionA), $this->normalize($versionB));
    }

    public function isUpgradeAvailable(?string $latest = null): bool
    {
        $latest ??= $this->latest();

        if (! $latest) {
            return false;
        }

        return $this->compare($latest, $this->current()) > 0;
    }

    public function setCurrent(string $version): void
    {
        $this->settings->set('app_version', $version, 'string', 'version');
    }

    public function setLatest(?string $version): void
    {
        if ($version) {
            $this->settings->set('latest_version', $version, 'string', 'version');
        }
    }

    public function checkRemote(): ?array
    {
        $url = $this->settings->get('update_server_url') ?: config('system.updates.remote_check_url');

        if (! $url || ! ($this->settings->get('update_check_enabled') ?? config('system.updates.remote_check_enabled'))) {
            return null;
        }

        try {
            $response = Http::timeout(15)->get($url, [
                'current_version' => $this->current(),
            ]);

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();

            if (! empty($data['version'])) {
                $this->setLatest($data['version']);
            }

            return $data;
        } catch (\Throwable) {
            return null;
        }
    }

    public function isCompatible(string $minimum, ?string $maximum = null, ?string $current = null): bool
    {
        $current ??= $this->current();

        if ($this->compare($current, $minimum) < 0) {
            return false;
        }

        if ($maximum && $this->compare($current, $maximum) > 0) {
            return false;
        }

        return true;
    }

    private function normalize(string $version): string
    {
        return ltrim($version, 'vV');
    }
}
