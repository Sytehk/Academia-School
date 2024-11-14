<style type="text/css">
    #id_card::after {
        content: "";
        width: 100%;
        height: 100%;
        position: absolute;
        opacity: 0.2;
        top: 0;
        background-image: url('{{ get_logo() }}') !important;
        background-repeat: no-repeat !important;
        background-size: 100px auto !important;
        background-position: center !important;
    }

    .sidebar .sidebar-wrapper {
        background: {{ get_option('sidebar_color') != '' ? get_option('sidebar_color') : '#FFF' }};
    }

    .sidebar .nav li:not(.active)>a,
    .sidebar[data-background-color="white"] .nav li:not(.active)>a {
        color: {{ get_option('sidebar_text_color') != '' ? get_option('sidebar_text_color') : '#000' }};
    }

    .sidebar .logo .simple-text,
    .sidebar[data-background-color="white"] .logo .simple-text {
        color: {{ get_option('sidebar_text_color') != '' ? get_option('sidebar_text_color') : '#000' }};
    }

    .metismenu li {
        border-bottom: 1px solid {{ get_option('sidebar_border_color') != '' ? get_option('sidebar_border_color') : '#ddd' }};
    }

    @php $acb =get_option('active_sidebar_background')
    @endphp

    .sidebar[data-active-color="danger"] .nav li.active>a {
        background: {{ $acb != '' ? $acb : '#e74c3c' }};
    }

    .nav li.active>ul>li.active>a {
        color: {{ $acb != '' ? $acb : '#e74c3c' }} !important;
    }

    {!! get_option('custom_backend_css') !!} .div_one {
        position: relative;
    }

    .div_one::after {
        content: '';
        position: absolute;
        width: 2px;
        height: 45px;
        background-color: #fff;
        top: 10px;
        right: 0px;
    }

    .institute_and_year_div {
        background-color: #000;
        color: #fff;
        display: flex;
    }

    .institute_and_year_child_div {
        margin: 0;
        padding: 10px 5px;
        margin: 0;
        padding: 10px 5px;
    }

    .institute_div_heading {
        font-size: 12px;
        font-weight: 400;
        text-transform: uppercase;
        margin: 0;
        padding: 5px;
        font-size: 12px;
        font-weight: 400;
        text-transform: uppercase;
        margin: 0;
        padding: 5px;
    }

    .institute_div_span {
        padding: 0 5px;
        color: #29B2CA;
    }

    .divPaddingMargin {
        margin: 10px 0;
        padding: 0;
    }

    .institute_logo_img {
        width: 180px;
        height: 160px;
        border-radius: 5px;
        margin: 15px 0;
    }

    .institute_second_div_one {
        background-color: #00B09B !important;
        color: #fff !important;
        text-align: center;
    }

    .institute_second_div_two {
        background-color: #F06048 !important;
        color: #fff !important;
        text-align: center;
    }

    .institute_second_div_three {
        background-color: #1E7FFD !important;
        color: #fff !important;
        text-align: center;
    }

    .institute_second_div_four {
        background-color: #044BA5 !important;
        color: #fff !important;
        text-align: center;
    }

    .institute_second_div_one i,
    .institute_second_div_two i,
    .institute_second_div_three i,
    .institute_second_div_four i {
        font-size: 24px;
    }

    .institute_third_div img {
        max-width: 100%;
        max-height: 140px;
        border-radius: 5px;
        margin-top: 15px;
    }

    .institute_third_div .heading {
        margin: 0;
        padding: 0;
        margin: 10px 0;
    }

    .institute_third_div ul {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .institute_third_div ul li {
        margin-top: 5px;
    }

    .institute_third_div .right_div {
        background-color: #fff;
        width: 160px;
        height: 140px;
        border-radius: 5px;
        margin-top: 15px;
    }

    .institute_third_div .heading_four {
        padding-top: 30px;
        margin: 0;
        color: #000
    }

    .institute_third_div .heading_five {
        color: black;
        padding: 0;
    }

    .aside-custom-sidebar {
        background-color: #f4f3ef !important;
        box-shadow: none;
        border: none;
        padding: 0px 10px 0px 10px !important;
        margin-bottom: 0px !important;
    }

    /* Default styles */
    div.dt-container div.dt-length label {
        display: inline-block !important;
        float: left !important;
    }

    div.dt-container div.dt-search {
        display: inline-block !important;
        float: right !important;
    }

    /* Media query for screens less than or equal to 668px */
    @media (max-width: 668px) {
        div.dt-container div.dt-length label {
            float: none !important;
        }

        div.dt-container div.dt-search {
            float: none !important;
            margin-top: 10px;
        }
    }

    .modal-content {
        border: 1px solid var(--kt-modal-border-color) !important;
    }

    .select2-selection {
        height: 43px !important;
    }

    .card .card-header {
        padding: 0px !important;
        margin-bottom: 15px !important;
        min-height: 30px;
    }

    .card .card-header .card-title,
    .card .card-header .card-title .card-label {
        font-weight: 600 !important;
    }

    .swal2-popup .swal2-title {
        color: #fff !important;
    }

    @media (min-width: 300px) and (max-width: 667px) {
        .custom-margin {
            margin-top: 15px;
            text-align: center;
        }
    }

    .edit-icon svg path {
        fill: #007BFF;
        /* Blue color for edit icon */
    }

    .delete-icon svg path {
        fill: #FF0000;
        /* Red color for delete icon */
    }

    #User-table_wrapper .dt-length label {
        display: none !important;
    }

    .font_size_medium {
        font-size: 24px
    }

    .margin_top_24 {
        margin-top: 24px;
    }

    .icon_size {
        font-size: 62px;
    }

    .file_name {
        display: block;
        margin: 15px 0;
    }

    .span_m_p {
        display: block;
        margin: 15px 0;
    }

    .w_150 {
        width: 150px;
    }

    .frontend-visit-btn {
        height: 30px;
        min-width: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 2px;
        border-radius: 10px;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        color: #fff !important;
        text-decoration: none;
        font-size: 18px;
        font-weight: 600;
        box-shadow: 0 10px 30px rgba(37, 99, 235, 0.25);
        transition: all 0.3s ease;
    }

    .frontend-visit-btn i {
        font-size: 18px;
    }

    /* MOBILE HIDE */

    @media (max-width: 676px) {
        .only_pc_device {
            display: none !important;
        }

    }
</style>
