<?php

use App\Services\Installer\EnvFileWriter;
use Tests\TestCase;

uses(TestCase::class);

/**
 * quote()/unquote() are private but pure (no filesystem access), so we test
 * them directly via reflection rather than exercising set()/get() against a
 * real file — set() always targets base_path('.env') and must never touch
 * the test environment's actual .env file.
 */
function envQuote(string $value): string
{
    $method = (new ReflectionClass(EnvFileWriter::class))->getMethod('quote');
    $method->setAccessible(true);

    return $method->invoke(null, $value);
}

function envUnquote(string $value): string
{
    $method = (new ReflectionClass(EnvFileWriter::class))->getMethod('unquote');
    $method->setAccessible(true);

    return $method->invoke(null, $value);
}

it('quotes a value containing a double quote so it cannot break out of its line', function () {
    $result = envQuote('p"a"ss');

    expect($result)->toBe('"p\\"a\\"ss"');
    expect(preg_match('/^"(?:[^"\\\\]|\\\\.)*"$/', $result))->toBe(1);
});

it('escapes newlines so a multi-line value cannot inject a new env key', function () {
    $result = envQuote("value\nAPP_DEBUG=true");

    expect($result)->not->toContain("\n");
});

it('leaves a plain alphanumeric value unquoted', function () {
    expect(envQuote('127.0.0.1'))->toBe('127.0.0.1');
});

it('quotes an empty value', function () {
    expect(envQuote(''))->toBe('""');
});

it('round-trips a quoted value back to its original form', function () {
    $original = 'p"a\\ss with spaces';
    $quoted = envQuote($original);

    expect(envUnquote($quoted))->toBe($original);
});
