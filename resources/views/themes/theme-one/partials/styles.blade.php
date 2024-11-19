{{-- TAILWIND + local fonts (offline-safe) --}}
@include('partials.local-assets.head', ['font' => 'poppins', 'aos' => true])

{{-- FONT AWESOME included in partial above --}}

@if ($instituteImage)
    <link rel="shortcut icon" href="{{ asset('storage/institute_image_settings/' . $instituteImage->favicon) }}" />
@endif

{{-- Local Poppins font (offline-safe) --}}

<style>
    * {
        font-family: 'Poppins', sans-serif;
    }

    :root {

        /* PRIMARY BRAND */
        --color-primary: '#7c3aed';
        --color-secondary: '#0f172a';
        --color-accent: '#ec4899';

        /* TEXT SYSTEM */
        --text-primary: '#0f172a';
        --text-secondary: '#475569';
        --text-muted: '#94a3b8';

        /* BACKGROUND SYSTEM */
        --bg-primary: '#ffffff';
        --bg-secondary: '#f8fafc';

        /* GLASS SYSTEM */
        --glass: rgba(255, 255, 255, 0.12);
        --glass-border: rgba(255, 255, 255, 0.18);

        /* SHADOW SYSTEM */
        --shadow-soft: 0 10px 30px rgba(0, 0, 0, 0.08);
        --shadow-strong: 0 25px 60px rgba(0, 0, 0, 0.18);
    }

    html {
        scroll-behavior: smooth;
    }

    body {
        color: var(--color-text);
        background: #f8fafc;
        overflow-x: hidden;
    }

    .bg-primary {
        background: var(--color-primary);
    }

    .bg-secondary {
        background: var(--color-secondary);
    }

    .text-primary {
        color: var(--color-primary);
    }

    .border-primary {
        border-color: var(--color-primary);
    }

    .btn-primary {
        background: linear-gradient(135deg,
                var(--color-primary),
                var(--color-accent));
        color: white;
        padding: 12px 28px;
        border-radius: 999px;
        font-weight: 600;
        transition: all .4s ease;
        transform: translateZ(0);
        box-shadow: 0 10px 25px rgba(0, 0, 0, .15);
    }

    .btn-primary:hover {
        transform: translateY(-4px) scale(1.03);
        box-shadow: 0 20px 40px rgba(0, 0, 0, .25);
    }

    .glass {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(15px);
    }

    .section-title {
        font-size: 42px;
        font-weight: 800;
        line-height: 1.2;
    }

    .gradient-text {
        background: linear-gradient(90deg,
                var(--color-primary),
                #ec4899);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* ===============================
        CARD EFFECT
    =============================== */

    .theme-card {
        position: relative;
        border-radius: 24px;
        transform-style: preserve-3d;
        transition: all .5s ease;
        background: var(--glass);
        border: 1px solid var(--glass-border);
        backdrop-filter: blur(20px);
        will-change: transform;
    }

    .theme-card:hover {
        transform: translateY(-12px) rotateX(6deg) rotateY(-6deg);
        box-shadow: var(--shadow-strong);
    }

    /* LIGHT REFLECTION EFFECT */
    .theme-card::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg,
                rgba(255, 255, 255, .25),
                transparent,
                rgba(255, 255, 255, .05));
        opacity: 0;
        transition: .5s;
    }

    .theme-card:hover::before {
        opacity: 1;
    }

    /* ===============================
        MARQUEE
    =============================== */

    .marquee {
        overflow: hidden;
        white-space: nowrap;
    }

    .marquee-track {
        display: inline-block;
        padding-left: 100%;
        animation: marquee 25s linear infinite;
    }

    @keyframes marquee {

        0% {
            transform: translateX(0%);
        }

        100% {
            transform: translateX(-100%);
        }
    }

    /* ===============================
        FLOATING SHAPES
    =============================== */

    .shape {
        position: absolute;
        filter: blur(90px);
        opacity: .35;
        border-radius: 50%;
        transform: translateZ(0);
        animation: float3d 10s ease-in-out infinite;
    }

    .shape-1 {
        width: 250px;
        height: 250px;
        background: var(--color-primary);
        top: -50px;
        left: -50px;
    }

    .shape-2 {
        width: 350px;
        height: 350px;
        background: #ec4899;
        bottom: -120px;
        right: -120px;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(25px);
        border-radius: 24px;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-30px);
        }
    }

    /* ===============================
        HERO OVERLAY
    =============================== */

    .hero-overlay {
        background:
            linear-gradient(135deg,
                rgba(15, 23, 42, 0.82),
                rgba(124, 58, 237, 0.65));
    }

    /* ===============================
        NAV
    =============================== */

    .nav-link {
        position: relative;
        transition: .3s;
    }

    .nav-link::after {
        content: '';
        position: absolute;
        width: 0%;
        height: 2px;
        background: var(--color-primary);
        left: 0;
        bottom: -5px;
        transition: .3s;
    }

    .nav-link:hover::after {
        width: 100%;
    }

    /* ===============================
        STATS
    =============================== */

    .stats-box {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, .1);
    }

    /* ===============================
        FAQ
    =============================== */

    details summary {
        list-style: none;
    }

    details summary::-webkit-details-marker {
        display: none;
    }

    /* ===============================
        SCROLLBAR
    =============================== */

    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-thumb {
        background: var(--color-primary);
    }

    /* =========================
    3D SYSTEM CORE
    ========================= */

    html {
        scroll-behavior: smooth;
        perspective: 1200px;
    }

    body {
        background: var(--bg-primary);
        color: var(--text-primary);
    }

    /* 3D LAYER BASE */
    .card-3d {
        transform-style: preserve-3d;
        transition: transform .6s cubic-bezier(.2, .8, .2, 1);
        will-change: transform;
    }

    /* REAL 3D HOVER */
    .card-3d:hover {
        transform: rotateX(8deg) rotateY(-10deg) translateY(-10px);
    }

    /* INNER DEPTH */
    .card-3d .depth {
        transform: translateZ(40px);
    }

    /* FLOAT ANIMATION */
    .float-3d {
        animation: float3d 6s ease-in-out infinite;
    }

    @keyframes float3d {

        0%,
        100% {
            transform: translateY(0px) rotateX(0deg);
        }

        50% {
            transform: translateY(-20px) rotateX(5deg);
        }
    }

    .faq-item {
        position: relative;
        transform-style: preserve-3d;
        transition: all .4s ease;
        z-index: 1;
    }

    /* SAFE GLASS LAYER */
    .faq-item::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255, 255, 255, .08), transparent);
        opacity: 0;
        transition: .4s;
        z-index: 0;
        /* IMPORTANT */
        pointer-events: none;
        /* 🔥 CRITICAL FIX */
    }

    .faq-item:hover::before {
        opacity: 1;
    }

    /* CONTENT MUST STAY ABOVE LAYER */
    .faq-item>* {
        position: relative;
        z-index: 1;
    }

    /* OPTIONAL 3D HOVER */
    .faq-item:hover {
        transform: translateY(-6px) rotateX(2deg);
    }
</style>
