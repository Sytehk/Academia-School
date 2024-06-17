<?php

namespace App\Services\Installer;

use Illuminate\Support\Facades\File;

/**
 * Safely reads/updates key=value pairs in the .env file.
 *
 * Values are always written double-quoted with internal double-quotes,
 * backslashes and newlines escaped, so user-supplied data (DB password,
 * software name, etc.) can never break out of its line or inject/overwrite
 * a different key.
 */
class EnvFileWriter
{
    public static function path(): string
    {
        return base_path('.env');
    }

    /**
     * @param  array<string,string|int|bool|null>  $values
     */
    public static function set(array $values): void
    {
        $path = self::path();
        $content = File::exists($path) ? File::get($path) : '';

        foreach ($values as $key => $value) {
            $line = $key.'='.self::quote((string) $value);
            $pattern = '/^'.preg_quote($key, '/').'=.*$/m';

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, str_replace('\\', '\\\\', $line), $content);
            } else {
                $content = rtrim($content, "\n")."\n".$line."\n";
            }
        }

        File::put($path, $content);
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $path = self::path();
        if (! File::exists($path)) {
            return $default;
        }

        $content = File::get($path);
        if (preg_match('/^'.preg_quote($key, '/').'=(.*)$/m', $content, $m)) {
            return self::unquote(trim($m[1]));
        }

        return $default;
    }

    private static function quote(string $value): string
    {
        // Values containing whitespace or special .env characters must be quoted;
        // plain alphanumeric/host-like values are left bare for readability.
        if ($value === '' || preg_match('/[\s"\'#=\\\\]/', $value)) {
            $escaped = str_replace(['\\', '"', "\n", "\r"], ['\\\\', '\\"', '\\n', ''], $value);

            return '"'.$escaped.'"';
        }

        return $value;
    }

    private static function unquote(string $value): string
    {
        if (strlen($value) >= 2 && $value[0] === '"' && str_ends_with($value, '"')) {
            $inner = substr($value, 1, -1);

            return str_replace(['\\n', '\\"', '\\\\'], ["\n", '"', '\\'], $inner);
        }

        return $value;
    }
}
