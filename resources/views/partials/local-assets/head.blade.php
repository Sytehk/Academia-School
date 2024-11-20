{{-- Offline-safe assets: no external CDN dependencies --}}

{{-- Local fonts --}}
@if (!empty($font) && $font === 'poppins')
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/poppins-local.css') }}">
@else
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/inter-local.css') }}">
@endif

{{-- Pre-built Tailwind CSS (committed at public/assets/css/tailwind-app.css) --}}
@if (empty($skipTailwind))
    <link rel="stylesheet" href="{{ asset('assets/css/tailwind-app.css') }}">
@endif

{{-- Font Awesome --}}
@if (!empty($fontawesome) && $fontawesome === 'v4')
    <link rel="stylesheet" href="{{ asset('assets/plugins/font-awesome/css/font-awesome.min.css') }}">
@else
    <link rel="stylesheet" href="{{ asset('assets/vendor/font-awesome-6/all.min.css') }}">
@endif

@if (!empty($aos))
    <link rel="stylesheet" href="{{ asset('assets/vendor/aos/aos.css') }}">
@endif

@if (!empty($swiper))
    <link rel="stylesheet" href="{{ asset('assets/vendor/swiper/swiper-bundle.min.css') }}">
@endif

@if (!empty($bootstrap5))
    <link rel="stylesheet" href="{{ asset('assets/bootstrap-5/bootstrap.min.css') }}">
@endif
