<style type="text/css">
    body {
        font-size: 11px;
        background: #ccc;
    }

    .vital-form-view {
        width: 750px;
        background: #fff;
        padding: 10px;
        margin: 0px auto;
        padding-top: 10px;
    }

    .btn-print,
    .btn-download {
        background: #4da657;
        color: #fff;
        padding: 10px 20px;
        font-weight: bold;
        border: none;
        cursor: pointer;
    }

    .btn-hide-header {
        background: #6f4ac4;
        color: #fff;
        padding: 10px 20px;
        font-weight: bold;
        border: none;
        cursor: pointer;
    }

    .print-download-buttons {
        text-align: center;
        margin-bottom: 10px;
    }

    @media print {
        body {
            background-color: white;
        }

        .vital-form-view {
            width: 750px;
            background: #fff;
            padding: 10px;
            margin: 0px auto;
            border: none;
        }
    }

    @page {
        margin-top: 30px;
        margin-bottom: 0;
        margin-right: 0;
    }

    .remarks {
        border-bottom: 1px solid #ddd;
        font-size: 11px;
        line-height: 1.3;
        display: inline-block;
    }

    .remarks_data {
        font-size: 12px;
        line-height: 1.3;
        margin: 0;
    }

    .print_vh {
        min-height: calc(100vh - 15px) !important;
        border: 2px solid rgb(30, 0, 255);
    }

    .bg {
        background: #4d54a6 !important;
    }

    .text-justify {
        text-align: justify;
        display: block;
        width: 100%;
    }

    .text-center {
        text-align: center;
    }

    .text-truncate {
        white-space: nowrap;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .a,
    .b,
    .c,
    .d,
    .e {
        color: #4d54a6;
    }

    .a {
        font-size: 13px !important;
        font-weight: 500;
    }

    .c {
        font-size: 11px !important;
        font-weight: 400;
    }

    .d {
        font-size: 13px !important;
        font-weight: 500 !important;
        margin: 0 !important;
    }

    .e {
        font-size: 13px !important;
        font-weight: 500 !important;
        margin: 0 !important;
    }

    .box {
        border: 1px solid #4d54a6;
    }

    .box-back {
        font-weight: 700 !important;
        font-size: 16px !important;
        color: #4d54a6;
        background: #fff;
    }

    .border-bottom {
        border-bottom: 1px solid #15749c;
        padding-bottom: 2px;
    }

    .m-1,
    .my-1 {
        margin-top: 2px;
        margin-bottom: 2px;
    }

    .m-1,
    .mx-1 {
        margin-left: 5px;
        margin-right: 5px;
    }

    .m2,
    .my-2 {
        margin-top: 10px;
        margin-bottom: 10px;
    }

    .m2,
    .mx-2 {
        margin-left: 20px;
        margin-right: 20px;
    }

    .m-3,
    .my-3 {
        margin-top: 20px;
        margin-bottom: 20px;
    }

    .m-3,
    .mx-3 {
        margin-left: 20px;
        margin-right: 20px;
    }

    .m-4,
    .my-4 {
        margin-top: 40px;
        margin-bottom: 40px;
    }

    .m-4,
    .mx-4 {
        margin-right: 40px;
        margin-left: 40px;
    }

    .m-auto {
        margin: 0 auto;
    }

    .pr-0 {
        padding-right: 0 !important;
    }

    .space {
        justify-content: space-between;
        display: flex;
    }

    .over-flow {
        overflow-x: hidden;
    }

    .over-flow p {
        margin-top: 0px;
        margin-bottom: 0px;
    }

    .p-0 {
        padding: 0 !important;
    }

    .m-0 {
        margin: 0 !important;
    }

    .table {
        margin-bottom: 5px;
        width: 100%;
        border: 1px solid #4d54a6;
    }

    .table>thead>tr>th,
    .table>tbody>tr>th,
    .table>tfoot>tr>th,
    .table>thead>tr>td,
    .table>tbody>tr>td,
    .table>tfoot>tr>td {
        border: 1px solid #4d54a6;
        font-size: 10px;
        padding: 1px;
    }

    .table1>thead>tr>th,
    .table1>tbody>tr>th,
    .table1>tfoot>tr>th,
    .table1>thead>tr>td,
    .table1>tbody>tr>td,
    .table1>tfoot>tr>td {
        vertical-align: top;
        padding: 1px;
    }

    .letter_table tr th,
    .letter_table tr td {
        text-align: center;
    }

    .table1 {
        width: 100%;
    }

    .myTable tr th,
    .myTable tr td {
        font-size: 12px;
    }

    .print {
        position: fixed;
        bottom: 10px;
        right: 70px;
    }

    .an__T__b tr td {
        vertical-align: top;
    }

    .__im_st {
        text-align: center;
        display: block;
    }

    .__im_st img {
        margin: 10px auto;
        width: 150px;
    }

    .sports_area {
        justify-content: space-between;
        align-items: center;
        display: flex;
        margin-bottom: 20px;
    }

    .sports_area .items_box {
        display: inline-block;
        text-align: center;
    }

    .nowrap_th th {
        white-space: nowrap;
    }

    body {
        font-size: 11px;
    }

    @page {
        margin-top: 15px;
        margin-bottom: 0;
        margin-right: 0;
    }

    .remarks {
        border-bottom: 1px solid #ddd;
        font-size: 11px;
        line-height: 1.3;
        display: inline-block;
    }

    .remarks_data {
        font-size: 12px;
        line-height: 1.3;
        margin: 0;
    }

    .table>thead>tr>th,
    .table>tbody>tr>th,
    .table>tfoot>tr>th,
    .table>thead>tr>td,
    .table>tbody>tr>td,
    .table>tfoot>tr>td {
        padding: 1px !important
    }

    @media print {
        table .myTable {
            border-collapse: collapse;
            border: 1px solid rgb(222, 2, 167) !important;
            width: 100%;
        }

        .noprint {
            display: none !important;
        }

        .table>thead>tr>th,
        .table>tbody>tr>th,
        .table>tfoot>tr>th,
        .table>thead>tr>td,
        .table>tbody>tr>td,
        .table>tfoot>tr>td {
            padding: 1px !important;
        }
    }

    /* .print_vh {
        min-height: calc(100vh - 15px) !important;
        border: 1px solid #4d54a6 !important;
    } */

    .bg {
        background: #4d54a6 !important;
    }

    .text-justify {
        text-align: justify;
        display: block;
        width: 100%;
    }

    .text-center {
        text-align: center;
    }

    .text-truncate {
        white-space: nowrap;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .a,
    .b,
    .c,
    .d,
    .e {
        color: #4d54a6;
    }

    .a {
        font-size: 13px !important;
        font-weight: 500;
    }

    .c {
        font-size: 11px !important;
        font-weight: 400;
    }

    .d {
        font-size: 13px !important;
        font-weight: 500 !important;
        margin: 0 !important;
    }

    .e {
        font-size: 13px !important;
        font-weight: 500 !important;
        margin: 0 !important;
    }

    .box {
        border: 1px solid #4d54a6;
        padding: 2px;
    }

    .box-back {
        font-weight: 700 !important;
        font-size: 16px !important;
        padding: 2px;
        color: #4d54a6;
        background: #fff;
    }

    .border-bottom {
        border-bottom: 1px solid #15749c;
        padding-bottom: 2px;
    }

    .m-1,
    .my-1 {
        margin-top: 2px;
        margin-bottom: 2px;
    }

    .m-1,
    .mx-1 {
        margin-left: 5px;
        margin-right: 5px;
    }

    .m2,
    .my-2 {
        margin-top: 10px;
        margin-bottom: 10px;
    }

    .m2,
    .mx-2 {
        margin-left: 20px;
        margin-right: 20px;
    }

    .m-3,
    .my-3 {
        margin-top: 20px;
        margin-bottom: 20px;
    }

    .m-3,
    .mx-3 {
        margin-left: 20px;
        margin-right: 20px;
    }

    .m-4,
    .my-4 {
        margin-top: 40px;
        margin-bottom: 40px;
    }

    .m-4,
    .mx-4 {
        margin-right: 40px;
        margin-left: 40px;
    }

    .m-auto {
        margin: 0 auto;
    }

    .pr-0 {
        padding-right: 0 !important;
    }

    .space {
        justify-content: space-between;
        display: flex;
    }

    .over-flow {
        overflow-x: hidden;
    }

    .p-0 {
        padding: 0 !important;
    }

    .m-0 {
        margin: 0 !important;
    }

    .table {
        margin-bottom: 5px;
    }


    .table1>thead>tr>th,
    .table1>tbody>tr>th,
    .table1>tfoot>tr>th,
    .table1>thead>tr>td,
    .table1>tbody>tr>td,
    .table1>tfoot>tr>td {
        vertical-align: top;
        padding: 3px;
    }

    .letter_table tr th,
    .letter_table tr td {
        text-align: center;
    }

    .table1 {
        width: 100%;
    }

    .myTable tr th,
    .myTable tr td {
        font-size: 12px;
    }

    .print {
        position: fixed;
        bottom: 10px;
        right: 70px;
    }

    .an__T__b tr td {
        vertical-align: top;
    }

    .__im_st {
        text-align: center;
        display: block;
    }

    .__im_st img {
        margin: 10px auto;
        width: 150px;
    }

    .sports_area {
        justify-content: space-between;
        align-items: center;
        display: flex;
        margin-bottom: 20px;
    }

    .sports_area .items_box {
        display: inline-block;
        text-align: center;
    }

    .nowrap_th th {
        white-space: nowrap;
    }

    .new_footer table {
        color: rgba(255, 255, 255, 0.8);
        margin: 25px 0;
    }

    .new_footer table i {
        margin: 8px;
    }

    .new_footer_bottom img {
        width: 200px;
    }

    .new_footer_bottom table {
        width: 100%;
    }

    .new_footer_bottom table tr td a {
        padding: 3px 0;
        color: rgba(255, 255, 255, 0.4);
        display: block;
    }

    .new_footer_bottom2 {
        background: #1C1C1C;
    }

    ul.new_bottom_social {
        margin-left: 62px;
    }

    ul.new_bottom_social li {
        float: left;
        margin-right: 2px;
    }

    ul.new_bottom_social li a {
        width: 50px;
        height: 50px;
        display: block;
        background: #161616;
        text-align: center;
        line-height: 50px
    }

    ul.new_bottom_social li a.fb:hover {
        background: #3B5998;
    }

    ul.new_bottom_social li a.tw:hover {
        background: #00ACED;
    }

    ul.new_bottom_social li a.yo:hover {
        background: #BB0000;
    }

    ul.new_bottom_social li a.fl:hover {
        background: #FF0084;
    }

    /* body {
    margin: 0;
    padding: 0;
    font-size: 14px;
    background: white !important;
    font-family: "arial";
    } */
    .print_vh {
        min-height: calc(100vh - 15px) !important;
        border: none !important
    }

    .__im_st img {
        margin: 10px auto;
        width: 150px;
        height: 185px;
    }

    table {
        border-spacing: 0;
        border-collapse: separate;
        /* here is the devil */
    }

    .table>thead>tr>th,
    .table>tbody>tr>th,
    .table>tfoot>tr>th,
    .table>thead>tr>td,
    .table>tbody>tr>td,
    .table>tfoot>tr>td {
        border: 1px solid #4d54a6 !important;
        font-size: 10px;
        padding: 3px;
    }
</style>
