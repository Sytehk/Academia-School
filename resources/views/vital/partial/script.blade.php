<script src="{{ asset('assets/vendor/html2pdf/html2pdf.bundle.min.js') }}"></script>

<script type="text/javascript">
    function download() {
        var element = document.getElementById('print_vh');

        // Give a name.
        var opt = {
            margin: 0,
            filename: 'Vital-of-#{{ $student->roll ?? '' }}-{{ $student->first_name ?? '' }}-ID-.pdf',
            image: {
                type: 'jpeg',
                quality: 1
            },
            html2canvas: {
                scale: 4
            },
            jsPDF: {
                unit: 'in',
                format: 'letter',
                orientation: 'portrait'
            }
        };

        // New Promise-based usage:
        html2pdf().set(opt).from(element).save();
    }

    function printDiv() {
        var divContents = document.getElementById("print-full-area").innerHTML;
        var a = window.open('', '', 'height=1500, width=1500');
        a.document.write('<html>');
        a.document.write('<body >');
        a.document.write(divContents);
        a.document.write('</body></html>');
        a.document.close();
        a.print();
    }
</script>
