<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? _lang('Result') }}</title>
    @php
        $theme = ($header ?? [])['theme'] ?? [];
        $primary = $theme['primary_color'] ?? '#1a365d';
        $accent = $theme['accent_color'] ?? '#c9a227';
        $watermarkOpacity = $theme['watermark_opacity'] ?? 0.08;
        $bannerImage = ($header ?? [])['banner_image'] ?? null;
        $showWatermark = ($theme['show_watermark'] ?? true) && $bannerImage;
    @endphp
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #111;
            margin: 0;
            padding: 16px;
            font-size: 12px;
            position: relative;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            width: 70%;
            transform: translate(-50%, -50%);
            opacity: {{ $watermarkOpacity }};
            z-index: 0;
            pointer-events: none;
        }
        .watermark img {
            width: 100%;
            height: auto;
            object-fit: contain;
        }
        .sheet-wrap, .result-card {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.97);
        }
        .toolbar { text-align: right; margin-bottom: 12px; position: relative; z-index: 2; }
        .school-header { width: 100%; border-collapse: collapse; margin-bottom: 8px; border-bottom: 2px solid {{ $primary }}; }
        .school-name { font-size: 22px; font-weight: 700; letter-spacing: .3px; color: {{ $primary }}; text-transform: uppercase; }
        .school-tagline { font-size: 11px; color: #555; margin-top: 2px; }
        .school-logo img { height: 72px; max-width: 92px; object-fit: contain; }
        .school-meta { font-size: 11px; line-height: 1.55; width: 38%; vertical-align: middle; }
        .school-meta.right { text-align: right; }
        .school-logo { text-align: center; width: 24%; vertical-align: middle; }
        .doc-title {
            text-align: center;
            border: 2px solid {{ $primary }};
            padding: 8px 10px;
            font-weight: 700;
            margin: 10px 0 14px;
            text-transform: uppercase;
            color: {{ $primary }};
            background: linear-gradient(180deg, #fff 0%, #f7f9fc 100%);
            letter-spacing: .4px;
        }
        .info-table, .marks-table, .summary-table, .sign-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .info-table td { padding: 4px 8px; font-size: 12px; border: 1px solid #ddd; }
        .info-table tr:nth-child(even) td { background: #fafbfd; }
        .marks-table th, .marks-table td, .summary-table td, .sign-table td { border: 1px solid #222; padding: 5px 7px; }
        .marks-table th { background: {{ $primary }}; color: #fff; font-size: 11px; font-weight: 600; }
        .marks-table td { text-align: center; font-size: 11px; }
        .marks-table td.left { text-align: left; }
        .summary-table td { background: #f8fafc; font-weight: 600; }
        .summary-table .accent { color: {{ $accent }}; }
        .photo { width: 84px; height: 96px; object-fit: cover; border: 2px solid {{ $primary }}; }
        .muted { color: #555; }
        .fail { color: #a30000; font-weight: 700; }
        .pass { color: #0b6b2b; font-weight: 700; }
        .signatures td { height: 78px; text-align: center; vertical-align: bottom; font-size: 11px; border-top: 1px solid #333; }
        .signature-img { max-height: 48px; max-width: 120px; display: block; margin: 0 auto 4px; object-fit: contain; }
        .signature-line { border-top: 1px solid #333; width: 80%; margin: 0 auto; padding-top: 4px; font-weight: 600; }
        .domain-title {
            margin: 12px 0 6px;
            font-size: 12px;
            font-weight: 700;
            color: {{ $primary }};
            text-transform: uppercase;
            border-left: 4px solid {{ $accent }};
            padding-left: 8px;
        }
        .result-footer {
            margin-top: 8px;
            font-size: 10px;
            color: #666;
            text-align: center;
            border-top: 1px dashed #ccc;
            padding-top: 6px;
        }
        @page { margin: 12mm; }
        @media print {
            .toolbar { display: none !important; }
            body { padding: 0; }
            .result-card, .sheet-wrap { page-break-after: always; }
            thead { display: table-header-group; }
            tr { page-break-inside: avoid; }
        }
    </style>
    @yield('extra_css')
</head>
<body>
    @if ($showWatermark)
        <div class="watermark">
            <img src="{{ $bannerImage }}" alt="">
        </div>
    @endif
    @unless($hideToolbar ?? false)
        <div class="toolbar">
            <button onclick="window.print()">{{ _lang('Print') }}</button>
        </div>
    @endunless
    @yield('content')
</body>
</html>
