@php
    // Brand colours come from institute settings; fall back to a neutral corporate navy/blue.
    $idcAccent = $settings['primary_color'] ?? '#16325c';
    $idcAccent2 = $settings['secondary_color'] ?? '#2563eb';
@endphp
<style>
    :root {
        --idc-accent: {{ $idcAccent }};
        --idc-accent-2: {{ $idcAccent2 }};
        --idc-ink: #101a24;
        --idc-muted: #64748b;
        --idc-faint: #94a3b8;
        --idc-line: #e2e8f0;
        --idc-paper: #ffffff;
        --idc-wash: #f8fafc;
    }

    /* The shared PDF layout sets `word-break: break-all` on .form-view, which shreds
       names and addresses mid-letter. Cards opt out and wrap on word boundaries. */
    .idc-sheet,
    .idc-sheet * {
        word-break: normal;
        overflow-wrap: break-word;
        box-sizing: border-box;
    }

    /* ==========================================================================
       Toolbar (screen only)
       ========================================================================== */
    .idc-toolbar {
        text-align: center;
        margin-bottom: 16px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
        align-items: center;
    }

    .idc-toolbar button {
        background: var(--idc-accent);
        color: #fff;
        border: 1px solid var(--idc-accent);
        padding: 9px 16px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 13px;
        line-height: 1.2;
        cursor: pointer;
        font-family: Arial, Helvetica, sans-serif;
    }

    .idc-toolbar button.idc-btn-outline {
        background: #fff;
        color: var(--idc-accent);
    }

    .idc-toolbar button.idc-btn-outline.is-active {
        background: var(--idc-accent);
        color: #fff;
    }

    .idc-segment {
        display: inline-flex;
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid var(--idc-accent);
    }

    .idc-segment button {
        border-radius: 0;
        border: none;
        border-right: 1px solid var(--idc-accent);
    }

    .idc-segment button:last-child {
        border-right: none;
    }

    .idc-preview-label {
        font-size: 11px;
        color: var(--idc-muted);
        text-align: center;
        margin: 0 0 10px;
        text-transform: uppercase;
        letter-spacing: 0.09em;
        font-family: Arial, Helvetica, sans-serif;
    }

    /* ==========================================================================
       Sheet
       ========================================================================== */
    .idc-sheet {
        display: flex;
        flex-wrap: wrap;
        gap: 6mm;
        justify-content: flex-start;
        align-items: flex-start;
    }

    .idc-pair {
        display: flex;
        flex-direction: column;
        gap: 4mm;
        page-break-inside: avoid;
        break-inside: avoid;
    }

    /* ==========================================================================
       Card shell — CR80, 85.6 x 54 mm. Identical on screen and paper so the
       preview is a true 1:1 proof of the printed card.
       ========================================================================== */
    .idc-card {
        width: 85.6mm;
        height: 54mm;
        border-radius: 3mm;
        overflow: hidden;
        background: var(--idc-paper);
        box-shadow: 0 2px 10px rgba(16, 26, 36, 0.16);
        display: flex;
        flex-direction: column;
        font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
        color: var(--idc-ink);
        page-break-inside: avoid;
        break-inside: avoid;
        position: relative;
        /* Keep the accent bands when printing — browsers strip backgrounds otherwise. */
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* ==========================================================================
       Front — header band
       ========================================================================== */
    .idc-head {
        flex: none;
        background: linear-gradient(120deg, var(--idc-accent) 0%, var(--idc-accent-2) 100%);
        color: #fff;
        padding: 2.1mm 3mm;
        display: flex;
        align-items: center;
        gap: 2.2mm;
        min-height: 9.6mm;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .idc-logo {
        width: 7.4mm;
        height: 7.4mm;
        object-fit: contain;
        background: #fff;
        border-radius: 50%;
        padding: 0.7mm;
        flex: none;
    }

    .idc-head-text {
        min-width: 0;
        flex: 1;
        line-height: 1.2;
    }

    .idc-school-name {
        font-size: 8.6px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .idc-school-address {
        font-size: 5.6px;
        opacity: 0.82;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-top: 0.2mm;
    }

    .idc-card-label {
        font-size: 6.2px;
        font-weight: 600;
        opacity: 0.95;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-top: 0.3mm;
    }

    /* ==========================================================================
       Front — body: photo | identity | QR
       ========================================================================== */
    .idc-body {
        flex: 1;
        display: flex;
        gap: 2.4mm;
        padding: 2.4mm 3mm 0;
        min-height: 0;
        overflow: hidden;
    }

    .idc-photo-col {
        flex: none;
        width: 17mm;
    }

    .idc-photo {
        width: 17mm;
        height: 21mm;
        object-fit: cover;
        object-position: top center;
        border-radius: 1.2mm;
        border: 0.35mm solid var(--idc-line);
        background: var(--idc-wash);
        display: block;
    }

    .idc-identity {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
    }

    .idc-name {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        line-height: 1.14;
        letter-spacing: 0.01em;
        /* Two lines max, then ellipsis — long names must never push the grid down. */
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        max-height: 2.36em;
    }

    .idc-role {
        font-size: 7.4px;
        font-weight: 700;
        color: var(--idc-accent-2);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        line-height: 1.25;
        margin-top: 0.5mm;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .idc-rule {
        height: 0.3mm;
        background: var(--idc-line);
        margin: 1.3mm 0;
        flex: none;
    }

    .idc-fields {
        display: grid;
        grid-template-columns: 12.5mm 1fr;
        column-gap: 1.5mm;
        row-gap: 0.55mm;
        font-size: 7.2px;
        line-height: 1.35;
        align-content: start;
        overflow: hidden;
    }

    .idc-field-label {
        font-size: 6px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--idc-faint);
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding-top: 0.15mm;
    }

    .idc-field-value {
        font-weight: 700;
        color: var(--idc-ink);
        min-width: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* The ID / registration number — the field most often read off the card. */
    .idc-field-value.is-primary {
        font-size: 7.8px;
        color: var(--idc-accent);
        letter-spacing: 0.02em;
    }

    /* ---- QR column: fixed box, generous quiet zone, never overlaps ---- */
    .idc-qr-col {
        flex: none;
        width: 15.5mm;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.7mm;
    }

    .idc-qr-box {
        width: 15.5mm;
        height: 15.5mm;
        background: #fff;
        border: 0.3mm solid var(--idc-line);
        border-radius: 1mm;
        padding: 0.9mm;
        flex: none;
    }

    /* The QR is rendered as a data-URI <img> so it scales predictably here and in
       html2pdf/html2canvas, which mis-sizes raw inline <svg>. */
    .idc-qr {
        width: 100%;
        height: 100%;
        display: block;
    }

    .idc-qr-caption {
        font-size: 5.2px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--idc-faint);
        text-align: center;
        line-height: 1.25;
    }

    /* ==========================================================================
       Front — footer: validity | signature
       ========================================================================== */
    .idc-foot {
        flex: none;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 2mm;
        padding: 1.2mm 3mm 1.8mm;
        min-height: 7mm;
    }

    .idc-validity {
        font-size: 6.2px;
        color: var(--idc-muted);
        line-height: 1.3;
        min-width: 0;
    }

    .idc-validity b {
        display: block;
        font-size: 7px;
        color: var(--idc-ink);
    }

    .idc-validity-label {
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 5.4px;
        color: var(--idc-faint);
    }

    .idc-sign-block {
        flex: none;
        text-align: center;
        width: 24mm;
    }

    .idc-signature {
        height: 5.5mm;
        max-width: 22mm;
        object-fit: contain;
        display: block;
        margin: 0 auto 0.2mm;
    }

    .idc-sign-line {
        border-top: 0.3mm solid var(--idc-muted);
        margin: 0 auto 0.6mm;
        width: 22mm;
    }

    .idc-sign-spacer {
        height: 5.7mm;
    }

    .idc-sign-label {
        font-size: 5.6px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--idc-muted);
        border-top: 0.25mm solid var(--idc-line);
        padding-top: 0.5mm;
    }

    /* ==========================================================================
       Back
       ========================================================================== */
    .idc-back {
        justify-content: space-between;
    }

    .idc-back-strip {
        flex: none;
        background: linear-gradient(120deg, var(--idc-accent) 0%, var(--idc-accent-2) 100%);
        color: #fff;
        padding: 1.6mm 3mm;
        display: flex;
        align-items: center;
        gap: 1.8mm;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .idc-back-logo {
        width: 5mm;
        height: 5mm;
        object-fit: contain;
        background: #fff;
        border-radius: 50%;
        padding: 0.4mm;
        flex: none;
    }

    .idc-back-title {
        font-size: 7.4px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .idc-back-body {
        flex: 1;
        display: flex;
        gap: 2.4mm;
        padding: 2.2mm 3mm 0;
        min-height: 0;
        overflow: hidden;
    }

    .idc-back-main {
        flex: 1;
        min-width: 0;
    }

    .idc-back-heading {
        font-size: 6px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--idc-accent);
        margin: 0 0 0.8mm;
    }

    .idc-rules {
        list-style: none;
        margin: 0 0 1.4mm;
        padding: 0;
        font-size: 6.2px;
        line-height: 1.45;
        color: var(--idc-ink);
    }

    .idc-rules li {
        display: flex;
        gap: 1mm;
        margin-bottom: 0.4mm;
    }

    .idc-rules li::before {
        content: "\25AA";
        color: var(--idc-accent-2);
        flex: none;
    }

    .idc-emergency {
        font-size: 6.2px;
        line-height: 1.45;
        color: var(--idc-ink);
        background: var(--idc-wash);
        border-left: 0.6mm solid var(--idc-accent-2);
        border-radius: 0 0.8mm 0.8mm 0;
        padding: 1mm 1.4mm;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .idc-emergency b {
        display: block;
        font-size: 5.6px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--idc-muted);
        font-weight: 600;
    }

    .idc-back-aside {
        flex: none;
        width: 15.5mm;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.7mm;
    }

    .idc-back-foot {
        flex: none;
        background: var(--idc-accent);
        color: #fff;
        padding: 1.5mm 3mm;
        font-size: 5.8px;
        line-height: 1.45;
        text-align: center;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .idc-back-foot b {
        display: block;
        font-size: 6.2px;
        letter-spacing: 0.02em;
    }

    /* ==========================================================================
       Side toggle — both sides always exist in the DOM; this picks what is
       visible on screen AND what goes to the printer.
       ========================================================================== */
    body.idc-mode-front .idc-back {
        display: none;
    }

    body.idc-mode-back .idc-front {
        display: none;
    }

    /* ==========================================================================
       Screen-only preview chrome
       ========================================================================== */
    @media screen {
        body {
            background: #eef2f6;
        }

        .form-view {
            box-shadow: 0 1px 3px rgba(16, 26, 36, 0.1);
        }

        .idc-sheet {
            padding: 6mm;
            background: #fff;
            border-radius: 4px;
        }
    }

    /* Responsive preview only. Print output below is untouched by these rules. */
    @media screen and (max-width: 820px) {
        .form-view {
            width: auto !important;
            max-width: 100%;
            padding: 8px;
        }

        .idc-sheet {
            justify-content: center;
            padding: 4mm;
            gap: 5mm;
        }
    }

    @media screen and (max-width: 400px) {
        .idc-pair {
            /* Scale the whole card down rather than reflowing it, so the phone
               preview still shows the exact printed proportions. */
            transform: scale(0.84);
            transform-origin: top center;
            margin-bottom: -8mm;
        }
    }

    /* ==========================================================================
       Print
       ========================================================================== */
    @media print {
        @page {
            size: A4 portrait;
            margin: 9mm;
        }

        html,
        body {
            background: #fff !important;
            margin: 0;
            padding: 0;
        }

        .noprint,
        .idc-toolbar,
        .idc-preview-label {
            display: none !important;
        }

        /* Let the sheet use the full A4 text block instead of the 770px screen page. */
        .form-view {
            width: auto !important;
            max-width: none !important;
            padding: 0 !important;
            margin: 0 !important;
            box-shadow: none !important;
            background: transparent !important;
        }

        .idc-sheet {
            padding: 0;
            gap: 5mm;
            background: transparent;
            justify-content: flex-start;
        }

        .idc-pair {
            gap: 4mm;
        }

        .idc-card {
            /* Hairline cut guide; shadows would render as grey smudge on paper. */
            box-shadow: none;
            border: 0.2mm solid var(--idc-line);
        }
    }
</style>
