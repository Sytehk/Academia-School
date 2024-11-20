@props([
    'value',
    'size' => 220,
])

@php
    // Rendered at a high module count and scaled down by CSS, so the code stays
    // crisp on a 300dpi card printer. Emitted as a base64 data-URI <img> instead
    // of a raw inline <svg>: html2canvas (used by the "Download PDF" button)
    // ignores CSS sizing on inline SVG and would blow the card layout apart.
    //
    // Every card front AND back renders this component with the SAME value, so
    // a batch of N cards generated 2N identical QR codes. Memoisation lives in
    // qr_code_data_uri() (app/Helpers/general.php) rather than a static here --
    // this file is re-`require`d by Laravel's view engine on every render (by
    // design, so the same view can render again with different data), which
    // resets a `static` declared directly at this scope on every call.
    $encoded = qr_code_data_uri((string) $value, (int) $size);
@endphp

<img {{ $attributes->merge(['class' => 'idc-qr']) }} alt="{{ _lang('QR code') }}"
    src="data:image/svg+xml;base64,{{ $encoded }}">
