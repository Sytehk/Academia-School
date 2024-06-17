<?php

namespace App\Services\SystemUpdate;

use App\Exceptions\SystemUpdateException;
use Illuminate\Support\Facades\Artisan;

class UpdateCommandRunner
{
    public function run(array $metadata, UpdateLogger $logger): void
    {
        if (! ($metadata['run_commands'] ?? true)) {
            $logger->info('Post-update commands disabled in package metadata.');

            return;
        }

        $commands = $metadata['commands'] ?? [
            'optimize:clear',
        ];

        $allowed = config('system.updates.allowed_commands', []);

        foreach ($commands as $command) {
            $command = trim((string) $command);

            if (! in_array($command, $allowed, true)) {
                throw new SystemUpdateException("Command not allowed: {$command}", 'commands');
            }

            [$name, $params] = $this->parseCommand($command);

            Artisan::call($name, $params);
            $logger->info("Executed: php artisan {$command}");
        }
    }

    private function parseCommand(string $command): array
    {
        $parts = preg_split('/\s+/', $command) ?: [];
        $name = array_shift($parts) ?? '';
        $params = [];

        foreach ($parts as $part) {
            if (str_starts_with($part, '--')) {
                if (str_contains($part, '=')) {
                    [$key, $value] = explode('=', substr($part, 2), 2);
                    $params[$key] = $value;
                } else {
                    $params[substr($part, 2)] = true;
                }
            }
        }

        return [$name, $params];
    }
}
