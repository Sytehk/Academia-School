<?php

namespace App\Services\SystemUpdate;

use App\Exceptions\SystemUpdateException;
use Illuminate\Support\Facades\File;

class UpdateFileManager
{
    public function __construct(
        private readonly UpdatePackageValidator $validator,
    ) {}

    public function apply(string $extractPath, UpdateLogger $logger): int
    {
        $filesPath = $extractPath.DIRECTORY_SEPARATOR.'files';

        if (! is_dir($filesPath)) {
            throw new SystemUpdateException('Package files/ directory not found.', 'files');
        }

        $copied = 0;
        $basePath = base_path();
        $allowedExtensions = config('system.updates.allowed_file_extensions', ['php']);
        $protected = config('system.updates.protected_paths', []);

        foreach (File::allFiles($filesPath) as $file) {
            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($filesPath) + 1));
            $normalized = $this->validator->normalizePath($relative);

            if ($this->validator->isUnsafePath($normalized)) {
                throw new SystemUpdateException("Unsafe file path: {$relative}", 'files');
            }

            foreach ($protected as $protectedPath) {
                if (str_starts_with($normalized, $protectedPath.'/') || $normalized === $protectedPath) {
                    $logger->warning("Skipped protected file: {$normalized}");

                    continue 2;
                }
            }

            if (! $this->isAllowedExtension($normalized, $allowedExtensions)) {
                $logger->warning("Skipped disallowed file type: {$normalized}");

                continue;
            }

            $target = $basePath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $normalized);
            $targetDir = dirname($target);

            File::ensureDirectoryExists($targetDir);
            File::copy($file->getPathname(), $target);

            $copied++;
            $logger->info("Updated file: {$normalized}");
        }

        return $copied;
    }

    private function isAllowedExtension(string $path, array $allowed): bool
    {
        foreach ($allowed as $ext) {
            if (str_ends_with($path, '.'.$ext) || str_ends_with($path, $ext)) {
                return true;
            }
        }

        return false;
    }
}
