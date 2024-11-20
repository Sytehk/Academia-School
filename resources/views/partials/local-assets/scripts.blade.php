{{-- Offline-safe scripts: no external CDN dependencies --}}

@if (!empty($jquery))
    <script src="{{ asset('assets/vendor/jquery/jquery-3.7.1.min.js') }}"></script>
@endif

@if (!empty($alpine))
    <script defer src="{{ asset('assets/vendor/alpine/alpine.min.js') }}"></script>
@endif

@if (!empty($aos))
    <script src="{{ asset('assets/vendor/aos/aos.js') }}"></script>
@endif

@if (!empty($swiper))
    <script src="{{ asset('assets/vendor/swiper/swiper-bundle.min.js') }}"></script>
@endif

@if (!empty($bootstrap5))
    <script src="{{ asset('assets/bootstrap-5/bootstrap.bundle.min.js') }}"></script>
@endif

@if (!empty($chartjs))
    <script src="{{ asset('assets/vendor/chartjs/chart.umd.min.js') }}"></script>
@endif

@if (!empty($html2pdf))
    <script src="{{ asset('assets/vendor/html2pdf/html2pdf.bundle.min.js') }}"></script>
@endif

@if (!empty($html5qrcode))
    <script src="{{ asset('assets/vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>
@endif

@if (!empty($sweetalert2))
    <script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
@endif
