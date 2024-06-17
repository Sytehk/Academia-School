<?php

namespace App\Services\SystemUpdate;

use App\Contracts\SystemUpdateHookInterface;

class UpdateHookDispatcher
{
    /** @var SystemUpdateHookInterface[] */
    private array $hooks = [];

    public function __construct()
    {
        foreach (config('system.update_hooks', []) as $class) {
            if (class_exists($class) && is_subclass_of($class, SystemUpdateHookInterface::class)) {
                $this->hooks[] = app($class);
            }
        }
    }

    public function beforeUpdate(array $metadata): void
    {
        foreach ($this->hooks as $hook) {
            $hook->beforeUpdate($metadata);
        }
    }

    public function afterBackup(array $metadata, string $backupPath): void
    {
        foreach ($this->hooks as $hook) {
            $hook->afterBackup($metadata, $backupPath);
        }
    }

    public function beforeFilesUpdate(array $metadata, string $extractPath): void
    {
        foreach ($this->hooks as $hook) {
            $hook->beforeFilesUpdate($metadata, $extractPath);
        }
    }

    public function afterFilesUpdate(array $metadata): void
    {
        foreach ($this->hooks as $hook) {
            $hook->afterFilesUpdate($metadata);
        }
    }

    public function beforeMigration(array $metadata): void
    {
        foreach ($this->hooks as $hook) {
            $hook->beforeMigration($metadata);
        }
    }

    public function afterMigration(array $metadata): void
    {
        foreach ($this->hooks as $hook) {
            $hook->afterMigration($metadata);
        }
    }

    public function afterUpdate(array $metadata): void
    {
        foreach ($this->hooks as $hook) {
            $hook->afterUpdate($metadata);
        }
    }

    public function onUpdateFailed(array $metadata, \Throwable $exception): void
    {
        foreach ($this->hooks as $hook) {
            $hook->onUpdateFailed($metadata, $exception);
        }
    }
}
