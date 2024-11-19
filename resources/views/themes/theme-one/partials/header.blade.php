{{-- =====================================================
        HEADER MAIN
====================================================== --}}

<header class="main-header">
    {{-- LOGO AREA --}}
    <div class="header-container">
        <div class="logo-box">
            <a href="{{ url('/') }}">
                @if (!empty($instituteImage?->header_logo_light_theme))
                    <img src="{{ asset('storage/institute_image_settings/' . $instituteImage->header_logo_light_theme) }}"
                        class="school-logo">
                @else
                    <img src="{{ asset('assets/media/logos/default.png') }}" class="school-logo">
                @endif
            </a>
        </div>

        {{-- MOBILE MENU --}}
        <button id="openMobile" class="mobile-btn">

            ☰

        </button>

        {{-- CONTACT --}}
        <div class="header-right">
            <div class="phone">
                <span>
                    ☎
                </span>
                <div>
                    <small>
                        Call Us
                    </small>
                    <b>
                        <a href=""> {{ $settings['phone'] ?? '' }}</a>
                    </b>

                </div>
            </div>

            <a href="{{ url('/login') }}" class="login-btn">
                Login
            </a>
        </div>
    </div>

    {{-- =====================================================
            DESKTOP MENU
    ====================================================== --}}
    <nav class="desktop-menu">
        <a href="{{ url('/') }}">
            Home
        </a>
        <a href="#about">
            About
        </a>

        @if (($settings['admission_display_status'] ?? 'no') == 'yes')
            <a href="{{ route('admission-form-page') }}">
                Admission
            </a>
        @endif

        @if (($settings['exam_result_status'] ?? 'no') == 'yes')
            <a href="{{ route('result-page') }}">
                Results
            </a>
        @endif

        <a href="{{ route('academic-images') }}">
            Gallery
        </a>

        {{-- DROPDOWN --}}
        <div class="dropdown">
            <button>
                Academics ▾
            </button>

            <div class="dropdown-menu">
                @php
                    $pages = ($instituteId ?? null)
                        ? Modules\Frontend\Models\Page::where('institute_id', $instituteId)->get()
                        : collect();
                @endphp

                @foreach ($pages as $page)
                    <a href="{{ route('website.page', $page->slug) }}">
                        {{ $page->title }}
                    </a>
                @endforeach
            </div>
        </div>

        <a href="#events">
            Events
        </a>

        <a href="#faq">
            FAQ
        </a>

        <a href="{{ route('contact') }}">
            Contact
        </a>
    </nav>
</header>

{{-- =====================================================
        MOBILE OVERLAY
====================================================== --}}
<div id="mobileOverlay" class="mobile-overlay">
</div>

{{-- =====================================================
        MOBILE SIDEBAR
====================================================== --}}
<div id="mobileSidebar" class="mobile-sidebar">
    <div class="mobile-head">
        <h3>
            Menu
        </h3>
        <button id="closeMobile">
            ×
        </button>
    </div>

    <div class="mobile-links">
        <a href="/">
            Home
        </a>

        <a href="#about">
            About
        </a>

        @if (($settings['admission_display_status'] ?? 'no') == 'yes')
            <a href="{{ route('admission-form-page') }}">
                Admission
            </a>
        @endif

        @if (($settings['exam_result_status'] ?? 'no') == 'yes')
            <a href="{{ route('result-page') }}">
                Results
            </a>
        @endif

        <a href="{{ route('academic-images') }}">
            Gallery
        </a>

        <button id="mobileAcademics">
            Academics
            <span>
                ▼
            </span>
        </button>

        <div id="mobileAcademicBox" class="hidden">
            @foreach ($pages as $page)
                <a href="{{ route('website.page', $page->slug) }}">
                    {{ $page->title }}
                </a>
            @endforeach
        </div>

        <a href="#events">
            Events
        </a>

        <a href="#faq">
            FAQ
        </a>

        <a href="{{ route('contact') }}">
            Contact
        </a>

        <a href="/login" class="login-mobile">
            Login
        </a>
    </div>

</div>

<style>
    /* ================================
 TOP NOTICE
================================ */

    .top-notice {

        height: 50px;
        background: #803ef2;
        color: white;
        display: flex;
        align-items: center;
        overflow: hidden;

    }


    .notice-title {

        background: #18253f;
        padding: 15px 30px;
        font-weight: 700;

    }



    .notice-scroll {

        flex: 1;
        overflow: hidden;

    }


    .notice-track {

        white-space: nowrap;
        animation: move 15s linear infinite;

    }


    @keyframes move {

        from {
            transform: translateX(100%);
        }

        to {

            transform: translateX(-100%);

        }

    }






    /* ================================
 HEADER
================================ */
    .main-header {
        position: sticky;
        top: 0;
        z-index: 999;
        background: rgba(255, 255, 255, .92);
        backdrop-filter: blur(15px);
        box-shadow: 0 5px 20px #0001;

    }

    .header-container {
        max-width: 1200px;
        margin: auto;
        padding: 5px 0px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .school-logo {
        height: 70px;
        max-width: 250px;
        object-fit: contain;
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 35px;
    }

    .phone {
        display: flex;
        gap: 12px;
        align-items: center;
        color: #803ef2;
    }

    .phone span {
        font-size: 30px;
    }

    .login-btn,
    .login-mobile {
        background: #803ef2;
        color: white;
        padding: 13px 35px;
        border-radius: 50px;
    }

    /* ================================
 MENU
================================ */


    .desktop-menu {

        height: 65px;
        display: flex;
        justify-content: center;
        gap: 35px;
        align-items: center;
        border-top: 1px solid #eee;

    }


    .desktop-menu a,
    .dropdown button {

        background: none;
        border: 0;
        font-size: 15px;
        color: #111;

    }





    .dropdown {

        position: relative;

    }



    .dropdown-menu {

        position: absolute;
        top: 35px;
        left: 0;
        width: 220px;

        background: white;
        box-shadow: 0 15px 40px #0002;
        border-radius: 12px;

        opacity: 0;
        visibility: hidden;
        transition: .3s;


    }



    .dropdown:hover .dropdown-menu {


        opacity: 1;
        visibility: visible;


    }



    .dropdown-menu a {


        display: block;
        padding: 14px;


    }



    .dropdown-menu a:hover {

        background: #f5f5f5;

    }






    /* ================================
 MOBILE
================================ */


    .mobile-btn {

        display: none;

    }



    .mobile-overlay {

        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .55);
        z-index: 9998;
        display: none;

    }



    .mobile-sidebar {


        position: fixed;
        top: 0;
        left: -320px;

        width: 300px;
        height: 100vh;

        background: white;

        z-index: 9999;

        transition: .4s;

        box-shadow: 10px 0 30px #0004;


    }




    .mobile-sidebar.active {

        left: 0;

    }




    .mobile-head {

        display: flex;
        justify-content: space-between;
        padding: 20px;
        border-bottom: 1px solid #ddd;


    }



    .mobile-links {

        padding: 25px;
        display: flex;
        flex-direction: column;
        gap: 18px;


    }



    .mobile-links a,
    .mobile-links button {

        font-size: 16px;
        color: #111;

        text-align: left;
        background: none;
        border: 0;

    }





    #mobileAcademicBox {

        padding-left: 20px;

    }



    .hidden {

        display: none;

    }





    @media(max-width:900px) {

        /* hide desktop middle/right section */
        .header-right,
        .desktop-menu {
            display: none !important;
        }

        /* header row */

        .header-container {
            width: 100%;
            padding: 18px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }


        .school-logo {

            height: 55px;

        }


        .header-right {

            display: none;

        }


        .desktop-menu {

            display: none;

        }



        .mobile-btn {

            display: block;

            font-size: 30px;
            border: 0;
            background: none;


        }


        .main-header {

            padding-bottom: 10px;

        }

        /* logo left */

        .logo-box {

            order: 1;

        }


        .school-logo {

            height: 55px;
            max-width: 180px;

        }




        /* menu right */

        .mobile-btn {

            display: flex !important;

            order: 2;

            font-size: 32px;

            align-items: center;
            justify-content: center;

            color: #111;

            background: none;

            border: 0;

            cursor: pointer;

        }





        /* remove extra header height */

        .main-header {

            padding: 0;

        }





    }
</style>









<script>
    document.addEventListener("DOMContentLoaded", () => {


        let open = document.getElementById("openMobile");

        let close = document.getElementById("closeMobile");

        let side = document.getElementById("mobileSidebar");

        let overlay = document.getElementById("mobileOverlay");



        open.onclick = () => {


            side.classList.add("active");

            overlay.style.display = "block";

            document.body.style.overflow = "hidden";


        }



        function hide() {


            side.classList.remove("active");

            overlay.style.display = "none";

            document.body.style.overflow = "auto";


        }



        close.onclick = hide;

        overlay.onclick = hide;





        document.getElementById("mobileAcademics").onclick = () => {


            document.getElementById("mobileAcademicBox")
                .classList.toggle("hidden");


        }



    });
</script>
