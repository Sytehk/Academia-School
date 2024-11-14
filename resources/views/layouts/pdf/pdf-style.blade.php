{{-- Styles for PDF --}}
<style>
    body {
        /* font-size: 11px; */
        background: #ccc;
    }

    .pdf-page-title {
        font-size: 20px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 10px;
    }

    .form-view {
        width: 770px;
        height: auto;
        background: #fff;
        padding: 10px;
        margin: 0px auto;
        word-break: break-all;
    }

    .btn-print,
    .btn-download,
    .btn-back {
        background: #4da657;
        color: #fff;
        padding: 10px 20px;
        font-weight: bold;
        border: none;
        cursor: pointer;
    }

    .btn-back {
        background: #0000004a !important;
    }

    .print-download-buttons {
        text-align: center;
        margin-bottom: 10px;
    }

    .fw700 {
        font-weight: 700;
    }

    .d-flex {
        display: flex !important;
        justify-content: space-between !important;
    }

    .justify-center {
        justify-content: center !important;
    }

    .align-center {
        align-items: center !important;
    }

    .mt-4 {
        margin-top: 1rem;
    }

    .m-0 {
        margin: 0;
    }

    .p-0 {
        padding: 0;
    }

    .clearfix::after {
        content: "";
        display: table;
        clear: both;
    }

    .left-text {
        float: left;
        font-weight: 700;
    }

    .right-text {
        float: right;
        font-weight: 700;
    }

    .d-flex {
        display: flex
    }

    .justify-between {
        justify-content: space-between
    }

    li {
        list-style: none;
        text-align: left
    }

    @media print {
        .noprint {
            display: none !important;
        }
    }

    .flex {
        display: flex;
    }

    .block {
        display: block;
    }

    .flex-1 {
        flex: 1;
    }

    .float-left {
        float: left;
    }

    .float-right {
        float: right;
    }

    .clearfix {
        clear: both;
    }

    .text-center {
        text-align: center;
    }

    .text-left {
        text-align: left;
    }

    .text-right {
        text-align: right;
    }

    .text-bold {
        font-weight: bold;
    }

    .text-underline {
        text-decoration: underline;
    }

    .text-uppercase {
        text-transform: uppercase;
    }

    .text-capitalize {
        text-transform: capitalize;
    }

    .text-lowercase {
        text-transform: lowercase;
    }

    .text-italic {
        font-style: italic;
    }

    .text-justify {
        text-align: justify;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        max-width: auto;
        /* table-layout: fixed; */
        font-size: 11px;
    }

    .table {
        width: 100%;
        max-width: 100%;
        margin-bottom: 1rem;
        background-color: transparent;
    }

    .table-bordered {
        border: 1px solid #dee2e6;
    }

    .table-bordered td,
    .table-bordered th {
        border: 1px solid #dee2e6;
    }

    .table thead th {
        vertical-align: bottom;
        border-bottom: 2px solid #dee2e6;
    }

    .table td,
    .table th {
        padding: 4px;
        vertical-align: top;
        border-top: 1px solid #dee2e6;
    }

    .table-bordered thead td,
    .table-bordered thead th {
        border-bottom-width: 2px;
    }

    .bold {
        font-weight: bold;
    }

    .set-water-mark {
        background-image: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)), url("http://127.0.0.1:8000/images/watermark.png");
        background-repeat: no-repeat;
        background-position: center;
        background-size: contain;
    }

    .book_watermark {
        background-image: url("http://127.0.0.1:8000/images/png-book1.png");
        background-repeat: no-repeat;
        background-position: center;
        background-size: cover;
    }


    @media print {
        body {
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }

        .set-water-mark {
            background-image: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)), url("http://127.0.0.1:8000/images/watermark.png");
            background-repeat: no-repeat;
            background-position: center;
        }

        .book_watermark {
            background-image: url("http://127.0.0.1:8000/images/png-book1.png");
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
            width: 100%;
            padding-top: 40px;
            padding-bottom: 0;
            min-height: calc(100vh - 80px);
        }
    }
</style>
