<?php

use Illuminate\Support\Facades\Blade;
use SimpleSoftwareIO\QrCode\Generator;

/**
 * Every ID card renders the id-card.qr component TWICE with the SAME value
 * (once on the card front, once on the back), and a batch print renders one
 * card per student -- so a 200-student class generated 400 QR codes, all real
 * CPU work, when only 200 distinct values were ever asked for.
 *
 * qr_code_data_uri() (app/Helpers/general.php) is the actual unit of change:
 * the Blade component now does nothing but call it. A call-counting fake
 * Generator is bound into the container directly (Mockery on this facade hits
 * a "cannot redeclare" error in this environment, because QrCode's own
 * getFacadeAccessor() calls clearResolvedInstance() on every access).
 *
 * (An earlier version of this fix put the memoisation in a `static` declared
 * directly inside the Blade @php block. That measurably did NOT work: Laravel's
 * PhpEngine renders a compiled view via require -- not require_once, since the
 * same view legitimately needs to render again with different data -- and a
 * plain top-level `static` is reinitialised on every fresh require. Moving the
 * cache into a genuine function declaration, guarded by function_exists so it
 * is only ever defined once, is what actually persists a static across calls.)
 */
class CountingQrGeneratorFake extends Generator
{
    public int $calls = 0;

    public function generate(string $text, ?string $filename = null)
    {
        $this->calls++;

        return "<svg>{$text}</svg>";
    }
}

it('generates a QR code once per distinct value, not once per call', function () {
    $fake = new CountingQrGeneratorFake;
    app()->instance(Generator::class, $fake);

    // Front + back of the same student's card: identical value, called twice.
    $front = qr_code_data_uri('QR-STUDENT-1', 220);
    $back = qr_code_data_uri('QR-STUDENT-1', 220);
    // A second, different student.
    $other = qr_code_data_uri('QR-STUDENT-2', 220);

    expect($fake->calls)->toBe(2);
    expect($front)->toBe($back);
    expect($front)->not->toBe($other);
});

it('still renders a working QR image through the actual Blade component', function () {
    $html = Blade::render(
        '<x-id-card.qr :value="$value" />',
        ['value' => 'QR-REAL-RENDER']
    );

    expect($html)->toContain('data:image/svg+xml;base64,');
    expect($html)->toContain('idc-qr');
});
