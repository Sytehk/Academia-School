<?php

namespace App\Services\SystemUpdate;

use App\Exceptions\SystemUpdateException;
use Illuminate\Support\Facades\File;
use ZipArchive;

class UpdateExtractor
{
    public function __construct(
        private readonly UpdatePackageValidator $validator,
    ) {}

    public function extract(string $zipPath, string $destination): string
    {
        File::ensureDirectoryExists($destination);

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new SystemUpdateException('Failed to open update package for extraction.', 'extract');
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            $normalized = $this->validator->normalizePath($entryName);

            if ($this->validator->isUnsafePath($normalized)) {
                $zip->close();
                throw new SystemUpdateException("Unsafe path in archive: {$entryName}", 'extract');
            }

            $targetPath = $destination.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $normalized);
            $targetDir = dirname($targetPath);

            if (! str_starts_with(realpath($targetDir) ?: $targetDir, realpath($destination))) {
                // Ensure parent exists first for realpath check
                File::ensureDirectoryExists($targetDir);
            }

            $resolvedTarget = realpath($targetDir);
            $resolvedDest = realpath($destination);

            if ($resolvedTarget && $resolvedDest && ! str_starts_with($resolvedTarget, $resolvedDest)) {
                $zip->close();
                throw new SystemUpdateException("Path traversal blocked: {$entryName}", 'extract');
            }

            if (str_ends_with($normalized, '/')) {
                File::ensureDirectoryExists($targetPath);

                continue;
            }

            File::ensureDirectoryExists($targetDir);

            $contents = $zip->getFromIndex($i);
            if ($contents === false) {
                $zip->close();
                throw new SystemUpdateException("Failed to read archive entry: {$entryName}", 'extract');
            }

            file_put_contents($targetPath, $contents);
        }

        $zip->close();

        return $destination;
    }
}
