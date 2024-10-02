@extends('layouts.pdf.index')

@section('styles')
    <style>
        .form-view {
            width: 1040px;
            height: auto;
            background: #fff;
            padding: 10px;
            margin: 0px auto;
            padding-top: 10px;
            font-size: 11px;
        }

        tbody td,
        thead th {
            text-align: left;
        }

        /* tbody tr's first td */
        tbody tr td:first-child {
            min-width: 140px;
        }

        #code {
            display: none;
        }
    </style>
@endsection
@section('content-download')
    <div class="noprint print-download-buttons">
        @include('layouts.pdf.back-button')
        @include('layouts.pdf.print-button')
    </div>
    <div style="clear: both;"></div>
@endsection
@section('content')
    <div class="container" style="width:100%">
        <div class="">
            @if (isset($books) && count($books) > 0)
                @php
                    $barcodeCounter = 0;
                @endphp
                @foreach ($books as $book)
                    @if ($barcodeCounter % 3 == 0)
                        <div class="page-break"></div>
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 30px;">
                    @endif
                    @if ($barcodeCounter % 3 == 0 || $barcodeCounter == 0)
                        <tr>
                    @endif
                    <td width="20%">
                        <span
                            style="text-align: center; display:block; padding: 5px 10px; font-size: 18px; text-transform:uppercase;">{{ $book->book_name }}</span>
                        <span style="text-align: center; display:block; padding: 0 10px;">
                            {!! DNS1D::getBarcodeSVG($book->code, 'C39', 2, 50, '#2A3239') !!}
                        </span>
                        <span
                            style="text-align: center; display: block; font-size: 18px; margin-top: -10px;">{{ $book->code }}</span>
                    </td>
                    @if ($barcodeCounter % 3 == 2 || $loop->last)
                        </tr>
                    @endif
                    @php
                        $barcodeCounter++;
                    @endphp
                @endforeach
                </table>
            @else
                <p class="text-danger">No Books Found</p>
            @endif
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript">
        /**
         * Download PDF
         *
         * @param {string} fileName timestamp default
         * @param {string} elementId
         */
        function downloadPdf(fileName = 'Library Books Bercode Print', elementId =
            'print_vh') {
            var element = document.getElementById(elementId);

            if (fileName == null) {
                fileName = new Date().getTime();
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
                    orientation: 'landscape'
                },
                pagebreak: {
                    before: '.page2el'
                }
            };

            // New Promise-based usage:
            html2pdf().set(opt).from(element).save();
        }
    </script>
@endsection
