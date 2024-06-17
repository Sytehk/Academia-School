<?php

namespace App\Services\Installer;

class RequirementsChecker
{
    /** Extensions the application actually depends on (composer.json has no ext-* entries to derive this from). */
    private const REQUIRED_EXTENSIONS = [
        'openssl', 'pdo', 'pdo_mysql', 'mbstring', 'tokenizer', 'xml',
        'ctype', 'json', 'bcmath', 'fileinfo', 'curl', 'gd', 'zip',
    ];

    public static function minPhpVersion(): string
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);
        $constraint = $composer['require']['php'] ?? '^8.2';

        // Pull the first numeric version out of a Composer constraint like "^8.3" or ">=8.3.0".
        preg_match('/(\d+\.\d+(?:\.\d+)?)/', $constraint, $m);

        return $m[1] ?? '8.2.0';
    }

    /**
     * @return array<string,bool>
     */
    public static function check(): array
    {
        $minPhp = self::minPhpVersion();
        $results = [
            "PHP Version (>= {$minPhp})" => version_compare(PHP_VERSION, $minPhp, '>='),
        ];

        foreach (self::REQUIRED_EXTENSIONS as $ext) {
            $results[ucfirst($ext).' Extension'] = extension_loaded($ext);
        }

        return $results;
    }

    public static function allPass(array $results): bool
    {
        return ! in_array(false, $results, true);
    }

    /**
     * @return array<string,bool>
     */
    public static function checkPermissions(): array
    {
        $paths = [
            'storage/app' => storage_path('app'),
            'storage/framework/cache' => storage_path('framework/cache'),
            'storage/framework/sessions' => storage_path('framework/sessions'),
            'storage/framework/views' => storage_path('framework/views'),
            'storage/logs' => storage_path('logs'),
            'storage' => storage_path(''),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];

        $results = [];
        foreach ($paths as $label => $path) {
            if (! is_dir($path)) {
                @mkdir($path, 0755, true);
            }
            $results[$label] = is_dir($path) && is_writable($path);
        }

        // .env may not exist yet; if it does, it must be writable, and if it
        // doesn't, its parent (base_path) must be writable so we can create it.
        $results['.env'] = file_exists(base_path('.env'))
            ? is_writable(base_path('.env'))
            : is_writable(base_path());

        return $results;
    }
}
