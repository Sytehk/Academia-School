<?php

namespace App\Services\SystemUpdate;

use App\Exceptions\SystemUpdateException;
use ZipArchive;

class UpdatePackageValidator
{
    public function __construct(
        private readonly UpdateVersionManager $versionManager,
    ) {}

    public function validate(string $zipPath, ?string $expectedChecksum = null): array
    {
        if (! file_exists($zipPath)) {
            throw new SystemUpdateException('Update package not found.', 'validation');
        }

        $sizeMb = filesize($zipPath) / 1024 / 1024;
        $maxUpload = (int) config('system.updates.max_upload_size', 512);

        if ($sizeMb > $maxUpload) {
            throw new SystemUpdateException("Package exceeds maximum upload size of {$maxUpload}MB.", 'validation');
        }

        if ($expectedChecksum) {
            $actual = hash_file('sha256', $zipPath);
            if (! hash_equals(strtolower($expectedChecksum), strtolower($actual))) {
                throw new SystemUpdateException('Package checksum verification failed.', 'validation');
            }
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new SystemUpdateException('Unable to open update package.', 'validation');
        }

        $metadata = $this->readMetadataFromZip($zip);
        $structureChecks = $this->validateZipStructure($zip);

        $zip->close();

        $versionChecks = $this->versionManager->validateUpgrade($metadata);
        $versionChecks['file_structure'] = [
            'passed' => empty($structureChecks['errors']),
            'message' => empty($structureChecks['errors'])
                ? 'File structure valid'
                : implode('; ', $structureChecks['errors']),
        ];

        if (! empty($metadata['checksum'])) {
            $versionChecks['checksum'] = [
                'passed' => hash_file('sha256', $zipPath) === strtolower($metadata['checksum']),
                'message' => 'Package checksum verified',
            ];
        } else {
            $versionChecks['checksum'] = [
                'passed' => true,
                'message' => 'No checksum required',
            ];
        }

        $versionChecks['migration_compatibility'] = [
            'passed' => true,
            'message' => 'Migration compatibility will be verified during install',
        ];

        return [
            'metadata' => $metadata,
            'checks' => $versionChecks,
            'ready' => $this->versionManager->allPassed($versionChecks),
            'structure' => $structureChecks,
        ];
    }

    private function readMetadataFromZip(ZipArchive $zip): array
    {
        $content = $zip->getFromName('update.json');

        if ($content === false) {
            throw new SystemUpdateException('update.json not found in package.', 'validation');
        }

        $metadata = json_decode($content, true);

        if (! is_array($metadata) || empty($metadata['version'])) {
            throw new SystemUpdateException('Invalid update.json metadata.', 'validation');
        }

        return $metadata;
    }

    private function validateZipStructure(ZipArchive $zip): array
    {
        $errors = [];
        $totalUncompressed = 0;
        $maxFiles = (int) config('system.updates.max_files', 10000);
        $maxExtract = (int) config('system.updates.max_extract_size', 1024) * 1024 * 1024;
        $protected = config('system.updates.protected_paths', []);

        if ($zip->numFiles > $maxFiles) {
            $errors[] = "Archive contains too many files ({$zip->numFiles}).";

            return compact('errors');
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            $normalized = $this->normalizePath($entryName);

            if ($this->isUnsafePath($normalized)) {
                $errors[] = "Unsafe path: {$entryName}";

                continue;
            }

            foreach ($protected as $protectedPath) {
                $protectedNorm = $this->normalizePath($protectedPath);
                if ($this->pathTargetsProtected($normalized, $protectedNorm)) {
                    $errors[] = "Protected path targeted: {$entryName}";
                }
            }

            $stat = $zip->statIndex($i);
            $totalUncompressed += $stat['size'] ?? 0;

            if ($totalUncompressed > $maxExtract) {
                $errors[] = 'Archive exceeds maximum uncompressed size (possible zip bomb).';
                break;
            }
        }

        return compact('errors');
    }

    public function normalizePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#/+#', '/', $path) ?? $path;

        while (str_contains($path, '../') || str_starts_with($path, '../')) {
            $path = str_replace('../', '', $path);
        }

        return ltrim($path, '/');
    }

    public function isUnsafePath(string $path): bool
    {
        if ($path === '' || str_contains($path, '../') || str_starts_with($path, '/')) {
            return true;
        }

        if (preg_match('/^[A-Za-z]:/', $path)) {
            return true;
        }

        return false;
    }

    private function pathTargetsProtected(string $entryPath, string $protectedPath): bool
    {
        if (str_starts_with($entryPath, 'files/'.$protectedPath)) {
            return true;
        }

        return str_starts_with($entryPath, $protectedPath.'/') || $entryPath === $protectedPath;
    }
}
