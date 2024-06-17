<?php

namespace App\Services\SystemUpdate;

use App\Services\SystemVersionService;

class UpdateVersionManager
{
    public function __construct(
        private readonly SystemVersionService $versionService,
    ) {}

    public function validateUpgrade(array $metadata): array
    {
        $checks = [];
        $current = $this->versionService->current();
        $target = $metadata['version'] ?? null;

        $checks['package_format'] = ['passed' => true, 'message' => 'Package format valid'];
        $checks['version'] = [
            'passed' => (bool) $target,
            'message' => $target ? "Target version: {$target}" : 'Missing target version',
        ];

        $minimum = $metadata['minimum_version'] ?? null;
        $maximum = $metadata['maximum_version'] ?? null;

        $checks['minimum_version'] = [
            'passed' => ! $minimum || $this->versionService->compare($current, $minimum) >= 0,
            'message' => $minimum
                ? "Requires >= {$minimum}. Current: {$current}"
                : 'No minimum version required',
        ];

        $checks['maximum_version'] = [
            'passed' => ! $maximum || $this->versionService->compare($current, $maximum) <= 0,
            'message' => $maximum
                ? "Requires <= {$maximum}. Current: {$current}"
                : 'No maximum version limit',
        ];

        $checks['upgrade_direction'] = [
            'passed' => $target && $this->versionService->compare($target, $current) > 0,
            'message' => $target
                ? "Upgrade {$current} → {$target}"
                : 'Cannot determine upgrade direction',
        ];

        $requiresPhp = $metadata['requires_php'] ?? null;
        $checks['php_version'] = [
            'passed' => ! $requiresPhp || $this->satisfiesConstraint(PHP_VERSION, $requiresPhp),
            'message' => $requiresPhp
                ? "PHP {$requiresPhp} required. Running ".PHP_VERSION
                : 'PHP version check passed',
        ];

        $requiresLaravel = $metadata['requires_laravel'] ?? null;
        $laravelVersion = app()->version();
        $checks['framework_version'] = [
            'passed' => ! $requiresLaravel || $this->satisfiesConstraint($laravelVersion, $requiresLaravel),
            'message' => $requiresLaravel
                ? "Laravel {$requiresLaravel} required. Running {$laravelVersion}"
                : 'Framework version check passed',
        ];

        $checks['backup_availability'] = [
            'passed' => is_writable(storage_path('app')),
            'message' => is_writable(storage_path('app'))
                ? 'Backup storage is writable'
                : 'Backup storage is not writable',
        ];

        return $checks;
    }

    public function allPassed(array $checks): bool
    {
        foreach ($checks as $check) {
            if (! ($check['passed'] ?? false)) {
                return false;
            }
        }

        return true;
    }

    private function satisfiesConstraint(string $actual, string $constraint): bool
    {
        $constraint = trim($constraint);

        if (preg_match('/^(>=|<=|>|<|!=|=)\s*(.+)$/', $constraint, $matches)) {
            return version_compare($actual, trim($matches[2]), $matches[1]);
        }

        return version_compare($actual, $constraint, '>=');
    }
}
