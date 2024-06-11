<?php

namespace App\Contracts;

interface SystemUpdateHookInterface
{
    public function beforeUpdate(array $metadata): void;

    public function afterBackup(array $metadata, string $backupPath): void;

    public function beforeFilesUpdate(array $metadata, string $extractPath): void;

    public function afterFilesUpdate(array $metadata): void;

    public function beforeMigration(array $metadata): void;

    public function afterMigration(array $metadata): void;

    public function afterUpdate(array $metadata): void;

    public function onUpdateFailed(array $metadata, \Throwable $exception): void;
}
