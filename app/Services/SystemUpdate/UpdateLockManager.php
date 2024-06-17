<?php

namespace App\Services\SystemUpdate;

use App\Exceptions\SystemUpdateException;
use Illuminate\Support\Facades\Cache;

class UpdateLockManager
{
    public function acquire(?int $ttl = null): void
    {
        $key = config('system.updates.lock_key', 'system_update_lock');
        $ttl ??= (int) config('system.updates.lock_ttl', 3600);

        if (! Cache::add($key, [
            'started_at' => now()->toIso8601String(),
            'pid' => getmypid(),
        ], $ttl)) {
            throw new SystemUpdateException('Another system update is currently in progress.', 'lock');
        }
    }

    public function release(): void
    {
        Cache::forget(config('system.updates.lock_key', 'system_update_lock'));
    }

    public function isLocked(): bool
    {
        return Cache::has(config('system.updates.lock_key', 'system_update_lock'));
    }
}
