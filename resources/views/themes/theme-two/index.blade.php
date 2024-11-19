{{-- =========================================================
SMART SCHOOL STYLE THEME
FULL HOMEPAGE TEMPLATE
========================================================= --}}

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        {{ $settings['site_title'] ?? ($settings['school_name'] ?? 'School Website') }}
    </title>

    @include('partials.local-assets.head', [
        'font' => 'poppins',
        'aos' => true,
        'swiper' => true,
        'bootstrap5' => true,
        'skipTailwind' => true,
    ])

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        :root {
            --primary: #c2185b;
            --secondary: #16213e;
            --accent: #f5b400;
            --light: #f8f9fa;
            --text: #2b2b2b;
        }

        body {
            overflow-x: hidden;
            color: var(--text);
        }

        a {
            text-decoration: none;
        }

        .topbar {
            background: var(--secondary);
            color: white;
            font-size: 13px;
            padding: 8px 0;
        }

        .topbar a {
            color: white;
        }

        .navbar-area {
            background: white;
            box-shadow: 0 3px 10px rgba(0, 0, 0, .06);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .navbar-nav .nav-link {
            color: #222;
            font-weight: 500;
            padding: 28px 14px !important;
            font-size: 14px;
        }

        .navbar-nav .nav-link:hover {
            color: var(--primary);
        }

        .btn-theme {
            background: var(--primary);
            color: white;
            padding: 10px 25px;
            border-radius: 30px;
            transition: .3s;
            font-size: 14px;
            font-weight: 600;
        }

        .btn-theme:hover {
            background: var(--secondary);
            color: white;
        }

        .hero-slider {
            position: relative;
        }

        .hero-slide {
            min-height: 650px;
            position: relative;
            display: flex;
            align-items: center;
        }

        .hero-slide img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, .45);
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 600px;
            color: white;
        }

        .hero-content h1 {
            font-size: 58px;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 20px;
        }

        .hero-content p {
            font-size: 17px;
            line-height: 1.8;
            margin-bottom: 30px;
        }

        .feature-box {
            background: var(--primary);
            color: white;
            padding: 25px;
            transition: .3s;
            height: 100%;
        }

        .feature-box:hover {
            transform: translateY(-5px);
        }

        .feature-box i {
            font-size: 35px;
            margin-bottom: 15px;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .section-title p {
            color: #666;
        }

        .about-image img {
            width: 100%;
            border-radius: 10px;
        }

        .accordion-button:not(.collapsed) {
            background: var(--primary);
            color: white;
        }

        .course-card {
            background: white;
            border: 1px solid #eee;
            transition: .3s;
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
        }

        .course-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .course-content {
            padding: 25px;
        }

        .course-content h4 {
            font-size: 22px;
            font-weight: 700;
        }

        .achievement-section {
            background: var(--secondary);
            color: white;
        }

        .counter-box {
            border: 1px solid rgba(255, 255, 255, .1);
            padding: 35px;
            text-align: center;
            margin-bottom: 20px;
        }

        .counter-box h2 {
            font-size: 48px;
            font-weight: 800;
            color: var(--accent);
        }

        .teacher-card {
            text-align: center;
        }

        .teacher-card img {
            width: 220px;
            height: 220px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 20px;
        }

        .testimonial-section {
            background: var(--secondary);
            color: white;
        }

        .testimonial-card {
            border: 1px solid rgba(255, 255, 255, .1);
            padding: 40px;
            text-align: center;
        }

        .testimonial-card img {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 20px;
        }

        footer {
            background: #0f172a;
            color: white;
            position: relative;
        }

        footer::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, .7);
        }

        footer .container {
            position: relative;
            z-index: 2;
        }

        .footer-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 25px;
        }

        .footer-links a {
            display: block;
            color: #ddd;
            margin-bottom: 12px;
            transition: .3s;
        }

        .footer-links a:hover {
            color: var(--accent);
        }

        .social-icons a {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 10px;
        }

        .social-icons a:hover {
            background: var(--primary);
        }

        .marquee {
            overflow: hidden;
            white-space: nowrap;
        }

        .marquee-content {
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
    </style>

</head>

<body>

    {{-- =========================================================
    TOPBAR
    ========================================================= --}}
    <div class="topbar">

        <div class="container">

            <div class="row align-items-center">

                <div class="col-lg-6">

                    <div class="marquee">

                        <div class="marquee-content">

                            @foreach ($data['notices'] as $notice)
                                <span class="me-5">
                                    🔥 {{ $notice->title }}
                                </span>
                            @endforeach

                        </div>

                    </div>

                </div>

                <div class="col-lg-6 text-end">

                    <span class="me-4">
                        <i class="fa fa-envelope"></i>
                        {{ $settings['email'] ?? '' }}
                    </span>

                    <span>
                        <i class="fa fa-phone"></i>
                        {{ $settings['phone'] ?? '' }}
                    </span>

                </div>

            </div>

        </div>

    </div>

    {{-- =========================================================
    NAVBAR
    ========================================================= --}}
    <div class="navbar-area">

        <div class="container">

            <nav class="navbar navbar-expand-lg">

                <a class="navbar-brand" href="#">

                    @if (!empty($settings['logo']))
                        <img src="{{ asset('storage/logos/' . $settings['logo']) }}" style="height:70px">
                    @endif

                </a>

                <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#mainMenu">

                    <i class="fa fa-bars"></i>

                </button>

                <div class="collapse navbar-collapse" id="mainMenu">

                    <ul class="navbar-nav mx-auto">

                        <li class="nav-item">
                            <a href="#" class="nav-link">Home</a>
                        </li>

                        <li class="nav-item">
                            <a href="#about" class="nav-link">About</a>
                        </li>

                        <li class="nav-item">
                            <a href="#courses" class="nav-link">Courses</a>
                        </li>

                        <li class="nav-item">
                            <a href="#teachers" class="nav-link">Teachers</a>
                        </li>

                        <li class="nav-item">
                            <a href="#events" class="nav-link">Events</a>
                        </li>

                        <li class="nav-item">
                            <a href="#testimonials" class="nav-link">Testimonials</a>
                        </li>

                        <li class="nav-item">
                            <a href="#contact" class="nav-link">Contact</a>
                        </li>

                    </ul>

                    <a href="{{ url('/login') }}" class="btn-theme">
                        Login
                    </a>

                </div>

            </nav>

        </div>

    </div>

    {{-- =========================================================
    HERO SLIDER
    ========================================================= --}}
    <section class="hero-slider">

        <div class="swiper heroSwiper">

            <div class="swiper-wrapper">

                @foreach ($data['banners'] as $banner)
                    <div class="swiper-slide">

                        <div class="hero-slide">

                            <img src="{{ asset('storage/banners/' . $banner->image) }}">

                            <div class="hero-overlay"></div>

                            <div class="container">

                                <div class="hero-content" data-aos="fade-right">

                                    <h1>
                                        {{ $banner->title }}
                                    </h1>

                                    <p>
                                        {{ $banner->description }}
                                    </p>

                                    <a href="#" class="btn-theme">
                                        Learn More
                                    </a>

                                </div>

                            </div>

                        </div>

                    </div>
                @endforeach

            </div>

            <div class="swiper-pagination"></div>

        </div>

    </section>

    {{-- =========================================================
    FEATURES
    ========================================================= --}}
    <section class="py-5">

        <div class="container">

            <div class="row g-4">

                @foreach ($data['whyChooseUs'] as $item)
                    <div class="col-lg-4">

                        <div class="feature-box">

                            <i class="fa fa-graduation-cap"></i>

                            <h4>
                                {{ $item->title }}
                            </h4>

                            <p class="mb-0">
                                {{ $item->description }}
                            </p>

                        </div>

                    </div>
                @endforeach

            </div>

        </div>

    </section>

    {{-- =========================================================
    ABOUT
    ========================================================= --}}
    <section id="about" class="py-5">

        <div class="container">

            <div class="section-title">

                <h2>About Us</h2>

                <p>
                    Welcome To {{ $settings['school_name'] ?? '' }}
                </p>

            </div>

            <div class="row align-items-center g-5">

                <div class="col-lg-6">

                    <div class="about-image">

                        <img src="{{ asset('storage/about_us/' . $data['about']->image) }}">

                    </div>

                </div>

                <div class="col-lg-6">

                    <h3 class="mb-4">
                        {{ $data['about']->title }}
                    </h3>

                    <p class="mb-4">
                        {!! $data['about']->description !!}
                    </p>

                    <div class="accordion" id="accordionExample">

                        <div class="accordion-item">

                            <h2 class="accordion-header">

                                <button class="accordion-button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseOne">

                                    Why Choose Us?

                                </button>

                            </h2>

                            <div id="collapseOne" class="accordion-collapse collapse show">

                                <div class="accordion-body">
                                    Professional education management system.
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

    {{-- =========================================================
    COURSES
    ========================================================= --}}
    <section id="courses" class="py-5 bg-light">

        <div class="container">

            <div class="section-title">

                <h2>Our Main Courses</h2>

                <p>Professional academic programs</p>

            </div>

            <div class="row g-4">

                @foreach ($data['courses'] as $course)
                    <div class="col-lg-4">

                        <div class="course-card">

                            <img src="{{ asset('storage/courses/' . $course->image) }}">

                            <div class="course-content">

                                <small class="text-danger fw-bold">
                                    Science
                                </small>

                                <h4 class="mt-2">
                                    {{ $course->title }}
                                </h4>

                                <p>
                                    {{ Str::limit(strip_tags($course->description), 100) }}
                                </p>

                                <a href="#" class="btn-theme">
                                    Apply Now
                                </a>

                            </div>

                        </div>

                    </div>
                @endforeach

            </div>

        </div>

    </section>

    {{-- =========================================================
    ACHIEVEMENTS
    ========================================================= --}}
    <section class="achievement-section py-5">

        <div class="container">

            <div class="row align-items-center g-5">

                <div class="col-lg-6">

                    <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=1200"
                        class="img-fluid rounded">

                </div>

                <div class="col-lg-6">

                    <div class="row">

                        <div class="col-md-6">

                            <div class="counter-box">

                                <h2>{{ $data['teachers']->count() }}</h2>

                                <p>Teachers</p>

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="counter-box">

                                <h2>{{ $data['courses']->count() }}</h2>

                                <p>Courses</p>

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="counter-box">

                                <h2>{{ $data['events']->count() }}</h2>

                                <p>Events</p>

                            </div>

                        </div>

                        <div class="col-md-6">

                            <div class="counter-box">

                                <h2>{{ $data['testimonials']->count() }}</h2>

                                <p>Reviews</p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

    {{-- =========================================================
    TEACHERS
    ========================================================= --}}
    <section id="teachers" class="py-5">

        <div class="container">

            <div class="section-title">

                <h2>Our Experienced Staffs</h2>

            </div>

            <div class="row g-4">

                @foreach ($data['teachers'] as $teacher)
                    <div class="col-lg-3">

                        <div class="teacher-card">

                            <img src="{{ asset('storage/users/' . $teacher->user?->image) }}">

                            <h5>
                                {{ $teacher->name ?? $teacher->user?->name }}
                            </h5>

                            <p>
                                {{ $teacher->designation }}
                            </p>

                        </div>

                    </div>
                @endforeach

            </div>

        </div>

    </section>

    {{-- =========================================================
    TESTIMONIALS
    ========================================================= --}}
    <section id="testimonials" class="testimonial-section py-5">

        <div class="container">

            <div class="section-title text-white">

                <h2>What People Says</h2>

            </div>

            <div class="row g-4">

                @foreach ($data['testimonials'] as $testimonial)
                    <div class="col-lg-6">

                        <div class="testimonial-card">

                            <img src="{{ asset('storage/testimonials/' . $testimonial->thumbnail_image) }}">

                            <h5>
                                {{ $testimonial->name }}
                            </h5>

                            <small>
                                {{ $testimonial->note }}
                            </small>

                            <p class="mt-4">
                                {{ $testimonial->description }}
                            </p>

                        </div>

                    </div>
                @endforeach

            </div>

        </div>

    </section>

    {{-- =========================================================
    FOOTER
    ========================================================= --}}
    <footer id="contact" class="pt-5">

        <div class="container">

            <div class="row g-5 pb-5">

                <div class="col-lg-4">

                    <h3 class="footer-title">
                        {{ $settings['school_name'] ?? '' }}
                    </h3>

                    <p>
                        Professional school management system with modern learning solutions.
                    </p>

                    <div class="social-icons mt-4">

                        <a href="#">
                            <i class="fab fa-facebook-f"></i>
                        </a>

                        <a href="#">
                            <i class="fab fa-youtube"></i>
                        </a>

                        <a href="#">
                            <i class="fab fa-linkedin-in"></i>
                        </a>

                    </div>

                </div>

                <div class="col-lg-4">

                    <h3 class="footer-title">
                        Quick Links
                    </h3>

                    <div class="footer-links">

                        <a href="#">Home</a>
                        <a href="#">About</a>
                        <a href="#">Courses</a>
                        <a href="#">Teachers</a>
                        <a href="#">Events</a>

                    </div>

                </div>

                <div class="col-lg-4">

                    <h3 class="footer-title">
                        Contact Info
                    </h3>

                    <p>
                        <i class="fa fa-phone"></i>
                        {{ $settings['phone'] ?? '' }}
                    </p>

                    <p>
                        <i class="fa fa-envelope"></i>
                        {{ $settings['email'] ?? '' }}
                    </p>

                    <p>
                        <i class="fa fa-location-dot"></i>
                        {{ $settings['address'] ?? '' }}
                    </p>

                </div>

            </div>

            <div class="border-top border-secondary text-center py-4">

                {!! $settings['copyright_text'] ?? '© 2026 All Rights Reserved' !!}

            </div>

        </div>

    </footer>

    {{-- =========================================================
    SCRIPTS
    ========================================================= --}}
    @include('partials.local-assets.scripts', [
        'bootstrap5' => true,
        'swiper' => true,
        'aos' => true,
    ])

    <script>
        AOS.init();

        new Swiper(".heroSwiper", {
            loop: true,
            autoplay: {
                delay: 5000,
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
        });
    </script>

</body>

</html>
