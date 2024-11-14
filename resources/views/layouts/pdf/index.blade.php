<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>
        @yield('title') | {{ config('app.name') }}
    </title>

    {{-- Font awesome --}}
    <link rel="stylesheet" href="{{ asset('backend/css/font-awesome.min.css') }}">

    {{-- Styles for PDF --}}
    @include('layouts.pdf.pdf-style')

    {{-- Custom Styles --}}
    @yield('styles')

</head>

<body>
    @yield('content-download')

    <div id="print-full-area">
        <div class="form-view" id="print_vh">
            @yield('content')
        </div>
    </div>

    {{-- Scripts --}}
    <script src="{{ asset('assets/vendor/html2pdf/html2pdf.bundle.min.js') }}"></script>

    <script type="text/javascript">
        /**
         * Download PDF
         *
         * @param {string} fileName timestamp default
         * @param {string} elementId
         */
        function download(fileName = null, elementId = 'print_vh') {
            var element = document.getElementById(elementId);

            if (fileName == null) {
                // file name from page title.
                fileName = document.title;
            }

            // Give a name.
            var opt = {
                margin: 0,
                filename: fileName + '.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 3,
                    dpi: 96,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'A4',
                    orientation: 'portrait'
                },
                pagebreak: {
                    before: '.page2el'
                }
            };

            // New Promise-based usage:
            html2pdf().set(opt).from(element).save();
        }
    </script>

    @yield('scripts')
</body>

</html>
