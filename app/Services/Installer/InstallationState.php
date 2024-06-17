<?php

namespace App\Services\Installer;

use Illuminate\Support\Facades\File;

/**
 * Single source of truth for "is this app installed".
 *
 * Two independent signals must agree, so losing either one alone
 * (a storage/ wipe on deploy, or a hand-edited .env) doesn't flip the
 * app back into "reinstall mode" on a live site:
 *  - a marker file at storage/app/installed.json (survives .env edits)
 *  - APP_INSTALLED=true in .env (survives a storage/ wipe)
 *
 * The installer only ever transitions not-installed -> installed. There is
 * no code path that clears these once both are set.
 */
class InstallationState
{
    private static function markerPath(): string
    {
        return storage_path('app/installed.json');
    }

    /** Marker used by every release before this fix; kept for upgrade compatibility. */
    private static function legacyMarkerPath(): string
    {
        return storage_path('mightySchool');
    }

    public static function isInstalled(): bool
    {
        return File::exists(self::markerPath())
            || File::exists(self::legacyMarkerPath())
            || env('APP_INSTALLED') === true
            || env('APP_INSTALLED') === 'true';
    }

    public static function markInstalled(): void
    {
        File::ensureDirectoryExists(dirname(self::markerPath()));
        File::put(self::markerPath(), json_encode([
            'installed_at' => now()->toDateTimeString(),
            'app_version' => config('app.version', config('install.app_version', '1.0.0')),
        ], JSON_PRETTY_PRINT));

        // Kept alongside the new marker for compatibility with anything
        // (deploy scripts, health checks) still checking for the old file.
        File::put(self::legacyMarkerPath(), 'Welcome to MightySchool Software');

        EnvFileWriter::set(['APP_INSTALLED' => 'true']);
    }

    /**
     * Server-authoritative wizard progress, stored in the session so a user
     * cannot skip a step by typing a later URL directly. Independent of the
     * install lock above (this only matters pre-install).
     */
    private const STEPS = [
        'start',
        'requirements',
        'permissions',
        'database',
        'installation',
    ];

    public static function markStepComplete(string $step): void
    {
        $done = session('install_progress', []);
        $done[$step] = true;
        session(['install_progress' => $done]);
    }

    public static function hasCompleted(string $step): bool
    {
        return (bool) (session('install_progress', [])[$step] ?? false);
    }

    /**
     * True if every step strictly before $step is complete.
     */
    public static function canAccess(string $step): bool
    {
        $index = array_search($step, self::STEPS, true);
        if ($index === false || $index === 0) {
            return true;
        }

        for ($i = 0; $i < $index; $i++) {
            if (! self::hasCompleted(self::STEPS[$i])) {
                return false;
            }
        }

        return true;
    }

    public static function resetProgress(): void
    {
        session()->forget('install_progress');
    }
}
