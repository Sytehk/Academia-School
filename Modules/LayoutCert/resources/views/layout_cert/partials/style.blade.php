@php
    $certPrimary = $template->primary_color ?? '#1f3a5f';
    $certSecondary = $template->secondary_color ?? '#2f6fed';
    $certFont = $template->font_family ?? 'DejaVu Sans';
@endphp
<style>
    .form-view {
        font-family: '{{ $certFont }}', Arial, sans-serif;
        border: 1px solid #e2e6ea;
        position: relative;
        width: {{ ($template->orientation ?? 'portrait') === 'landscape' ? '1000px' : '780px' }};
        padding: 10px;
    }

    .cert-border {
        border: 2px solid {{ $certPrimary }};
        padding: 4px;
    }

    .cert-border-inner {
        border: 1px solid {{ $certSecondary }};
        padding: 26px 34px;
        @if (!empty($template->background_image))
            background-image: url('{{ asset('storage/certificates/'.$template->background_image) }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        @endif
    }

    .cert-title {
        text-align: center;
        font-size: 30px;
        font-weight: 700;
        letter-spacing: 0.04em;
        color: {{ $certPrimary }};
        text-transform: uppercase;
        margin: 6px 0 26px;
    }

    .cert-body p {
        font-size: 16px;
        line-height: 32px;
        text-align: justify;
        color: #16211e;
    }

    .cert-body b {
        color: {{ $certPrimary }};
    }

    @if ($template->show_watermark ?? true)
        .form-view::before {
            content: "{{ $template->watermark_text ?: get_option('school_name') }}";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 64px;
            font-weight: 700;
            color: {{ $certPrimary }};
            opacity: 0.06;
            white-space: nowrap;
            pointer-events: none;
            z-index: 0;
        }

        .cert-border,
        .cert-title,
        .cert-body,
        #certificate-header,
        #certificate-footer,
        .cert-verify-block {
            position: relative;
            z-index: 1;
        }
    @endif
</style>
