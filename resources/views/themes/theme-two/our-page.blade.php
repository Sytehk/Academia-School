{{-- =========================================
    Dynamic Theme Landing Page
========================================= --}}

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $settings['site_title'] ?? ($settings['school_name'] ?? 'School Website') }}</title>

    <meta name="description" content="{{ $settings['school_name'] ?? '' }}">

    @include('partials.local-assets.head', ['font' => 'poppins'])

    <style>
        :root {
            --primary-color: {{ $settings['primary_color'] ?? '#7c3aed' }};
            --secondary-color: {{ $settings['secondary_color'] ?? '#111827' }};
            --text-color: {{ $settings['text_color'] ?? '#ffffff' }};
        }

        body {
            background: var(--secondary-color);
            color: var(--text-color);
        }

        .primary-bg {
            background: var(--primary-color);
        }

        .primary-text {
            color: var(--primary-color);
        }

        .card-bg {
            background: #1f2937;
        }

        .section-bg {
            background: #111827;
        }
    </style>
</head>

<body>

    {{-- =========================================
        NAVBAR
    ========================================= --}}
    <header class="bg-black/80 backdrop-blur fixed top-0 left-0 w-full z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">

            <div class="flex items-center gap-3">

                @if (!empty($settings['logo']))
                    <img src="{{ asset($settings['logo']) }}" class="w-12 h-12 rounded-full object-cover">
                @endif

                <h2 class="text-2xl font-bold">
                    {{ $settings['school_name'] ?? 'School' }}
                </h2>
            </div>

            <nav class="hidden md:flex items-center gap-6">
                <a href="#home" class="hover:text-purple-400">Home</a>
                <a href="#about" class="hover:text-purple-400">About</a>
                <a href="#teachers" class="hover:text-purple-400">Teachers</a>
                <a href="#notices" class="hover:text-purple-400">Notices</a>
                <a href="#events" class="hover:text-purple-400">Events</a>
                <a href="#faq" class="hover:text-purple-400">FAQ</a>
            </nav>

            <a href="{{ url('/login') }}" class="primary-bg px-5 py-2 rounded-lg text-white hidden md:block">
                Login
            </a>

            <button id="menuBtn" class="md:hidden text-2xl">
                ☰
            </button>
        </div>

        {{-- MOBILE MENU --}}
        <div id="mobileMenu" class="hidden px-4 pb-5 md:hidden bg-black">
            <a href="#home" class="block py-2">Home</a>
            <a href="#about" class="block py-2">About</a>
            <a href="#teachers" class="block py-2">Teachers</a>
            <a href="#notices" class="block py-2">Notices</a>
            <a href="#events" class="block py-2">Events</a>
            <a href="#faq" class="block py-2">FAQ</a>
        </div>
    </header>

    {{-- =========================================
        HERO BANNER
    ========================================= --}}
    <section id="home">

        @if ($data['banners']->count() > 0)

            <div class="relative">

                @foreach ($data['banners'] as $banner)
                    <div class="h-screen bg-cover bg-center flex items-center justify-center"
                        style="background-image:url('{{ asset($banner->image) }}')">

                        <div class="bg-black/70 p-8 rounded-xl max-w-3xl text-center">

                            <h1 class="text-4xl md:text-6xl font-bold mb-5">
                                {{ $banner->title }}
                            </h1>

                            <p class="text-gray-300 text-lg mb-6">
                                {{ $banner->description }}
                            </p>

                            @if ($banner->button_text)
                                <a href="{{ $banner->button_link ?? '#' }}"
                                    class="primary-bg px-8 py-3 rounded-lg inline-block">
                                    {{ $banner->button_text }}
                                </a>
                            @endif

                        </div>
                    </div>
                @endforeach

            </div>

        @endif

    </section>

    {{-- =========================================
        ABOUT US
    ========================================= --}}
    @if ($data['about'])
        <section id="about" class="py-20 section-bg">

            <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 gap-12 items-center">

                <div>
                    <img src="{{ asset($data['about']->image) }}" class="rounded-2xl shadow-xl w-full">
                </div>

                <div>

                    <p class="primary-text mb-2">
                        About Us
                    </p>

                    <h2 class="text-4xl font-bold mb-5">
                        {{ $data['about']->title }}
                    </h2>

                    <p class="text-gray-300 leading-8">
                        {!! $data['about']->description !!}
                    </p>

                </div>

            </div>

        </section>
    @endif

    {{-- =========================================
        ACADEMIC IMAGES
    ========================================= --}}
    @if ($data['academicImages']->count() > 0)

        <section class="py-20 bg-gray-900">

            <div class="max-w-7xl mx-auto px-4">

                <div class="text-center mb-12">
                    <h2 class="text-4xl font-bold">
                        Academic Gallery
                    </h2>
                </div>

                <div class="grid md:grid-cols-3 gap-6">

                    @foreach ($data['academicImages'] as $image)
                        <div>
                            <img src="{{ asset($image->image) }}" class="rounded-xl h-72 w-full object-cover">
                        </div>
                    @endforeach

                </div>

            </div>

        </section>

    @endif

    {{-- =========================================
        WHY CHOOSE US
    ========================================= --}}
    @if ($data['whyChooseUs']->count() > 0)

        <section class="py-20 section-bg">

            <div class="max-w-7xl mx-auto px-4">

                <div class="text-center mb-14">
                    <h2 class="text-4xl font-bold primary-text">
                        Why Choose Us
                    </h2>
                </div>

                <div class="grid md:grid-cols-4 gap-6">

                    @foreach ($data['whyChooseUs'] as $item)
                        <div class="card-bg rounded-2xl p-6">

                            <h3 class="text-2xl font-semibold mb-4">
                                {{ $item->title }}
                            </h3>

                            <p class="text-gray-400">
                                {{ $item->description }}
                            </p>

                        </div>
                    @endforeach

                </div>

            </div>

        </section>

    @endif

    {{-- =========================================
        READY TO JOIN
    ========================================= --}}
    @if ($data['readyToJoinUs']->count() > 0)

        <section class="py-20 bg-gray-900">

            <div class="max-w-5xl mx-auto px-4">

                <div class="text-center mb-12">

                    <p class="primary-text">
                        Admissions
                    </p>

                    <h2 class="text-4xl font-bold">
                        Ready To Join Us?
                    </h2>

                </div>

                <div class="space-y-6">

                    @foreach ($data['readyToJoinUs'] as $item)
                        <div class="card-bg rounded-xl p-6 flex gap-5">

                            <div class="text-4xl">
                                🎓
                            </div>

                            <div>

                                <h3 class="text-xl font-semibold mb-2">
                                    {{ $item->title }}
                                </h3>

                                <p class="text-gray-400">
                                    {{ $item->description }}
                                </p>

                            </div>

                        </div>
                    @endforeach

                </div>

            </div>

        </section>

    @endif

    {{-- =========================================
        TEACHERS
    ========================================= --}}
    <section id="teachers" class="py-20 section-bg">

        <div class="max-w-7xl mx-auto px-4">

            <div class="text-center mb-14">

                <h2 class="text-4xl font-bold primary-text">
                    Our Teachers
                </h2>

            </div>

            @if ($data['teachers']->count() > 0)

                <div class="grid md:grid-cols-4 gap-6">

                    @foreach ($data['teachers'] as $teacher)
                        <div class="card-bg p-5 rounded-xl">

                            <div class="flex items-center gap-4">

                                <img src="{{ asset($teacher->image ?? 'default.png') }}"
                                    class="w-14 h-14 rounded-full object-cover">

                                <div>
                                    <h4 class="font-semibold">
                                        {{ $teacher->user->name ?? '' }}
                                    </h4>

                                    <p class="text-sm text-gray-400">
                                        {{ $teacher->designation ?? '' }}
                                    </p>
                                </div>

                            </div>

                            <p class="text-gray-400 text-sm mt-4">
                                {{ Str::limit($teacher->description, 100) }}
                            </p>

                        </div>
                    @endforeach

                </div>
            @else
                <div class="text-center text-gray-400">
                    No Teacher Found
                </div>

            @endif

        </div>

    </section>

    {{-- =========================================
        COURSES
    ========================================= --}}
    @if ($data['courses']->count() > 0)

        <section class="py-20 bg-gray-900">

            <div class="max-w-7xl mx-auto px-4">

                <div class="text-center mb-14">
                    <h2 class="text-4xl font-bold">
                        Our Courses
                    </h2>
                </div>

                <div class="grid md:grid-cols-3 gap-6">

                    @foreach ($data['courses'] as $course)
                        <div class="card-bg rounded-xl overflow-hidden">

                            <img src="{{ asset($course->image) }}" class="w-full h-60 object-cover">

                            <div class="p-5">

                                <h3 class="text-2xl font-semibold mb-3">
                                    {{ $course->title }}
                                </h3>

                                <p class="text-gray-400 mb-5">
                                    {{ Str::limit($course->description, 120) }}
                                </p>

                                <button class="primary-bg px-5 py-2 rounded-lg">
                                    View Details
                                </button>

                            </div>

                        </div>
                    @endforeach

                </div>

            </div>

        </section>

    @endif

    {{-- =========================================
        NOTICES
    ========================================= --}}
    <section id="notices" class="py-20 section-bg">

        <div class="max-w-7xl mx-auto px-4">

            <div class="text-center mb-14">

                <h2 class="text-4xl font-bold">
                    Latest Notices
                </h2>

            </div>

            <div class="space-y-5">

                @forelse ($data['notices'] as $notice)
                    <div class="bg-gray-800 rounded-xl p-6 flex flex-col md:flex-row justify-between gap-4">

                        <div>
                            <h3 class="text-xl font-semibold mb-2">
                                {{ $notice->title }}
                            </h3>

                            <p class="text-gray-400">
                                {{ Str::limit($notice->description, 120) }}
                            </p>
                        </div>

                        <div class="text-right">

                            <p class="text-gray-400 mb-4">
                                {{ \Carbon\Carbon::parse($notice->created_at)->format('d M Y') }}
                            </p>

                            @if ($notice->file)
                                <a href="{{ asset($notice->file) }}" target="_blank"
                                    class="primary-bg px-5 py-2 rounded-lg inline-block">
                                    Download
                                </a>
                            @endif

                        </div>

                    </div>

                @empty

                    <div class="text-center text-gray-400">
                        No Notices Available
                    </div>
                @endforelse

            </div>

        </div>

    </section>

    {{-- =========================================
        EVENTS
    ========================================= --}}
    <section id="events" class="py-20 bg-gray-900">

        <div class="max-w-7xl mx-auto px-4">

            <div class="text-center mb-14">

                <h2 class="text-4xl font-bold">
                    Upcoming Events
                </h2>

            </div>

            <div class="grid md:grid-cols-3 gap-8">

                @foreach ($data['events'] as $event)
                    <div class="card-bg rounded-xl p-6">

                        <div class="flex gap-4">

                            <div class="primary-bg px-4 py-3 rounded-lg text-center">

                                <h3 class="text-2xl font-bold">
                                    {{ \Carbon\Carbon::parse($event->date)->format('d') }}
                                </h3>

                                <p>
                                    {{ \Carbon\Carbon::parse($event->date)->format('M') }}
                                </p>

                            </div>

                            <div>

                                <h4 class="text-xl font-semibold">
                                    {{ $event->title }}
                                </h4>

                                <p class="text-gray-400 mt-2">
                                    {{ $event->location ?? '' }}
                                </p>

                                <p class="text-gray-400">
                                    {{ $event->time ?? '' }}
                                </p>

                            </div>

                        </div>

                    </div>
                @endforeach

            </div>

        </div>

    </section>

    {{-- =========================================
        MOBILE APP
    ========================================= --}}
    @if ($data['mobileAppSections']->count() > 0)

        @foreach ($data['mobileAppSections'] as $mobile)
            <section class="py-20 section-bg">

                <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-2 gap-12 items-center">

                    <div>
                        <img src="{{ asset($mobile->image) }}" class="rounded-xl">
                    </div>

                    <div>

                        <p class="primary-text">
                            Mobile App
                        </p>

                        <h2 class="text-4xl font-bold mb-6">
                            {{ $mobile->title }}
                        </h2>

                        <p class="text-gray-400 leading-8">
                            {{ $mobile->description }}
                        </p>

                    </div>

                </div>

            </section>
        @endforeach

    @endif


    {{-- =========================================
    TESTIMONIAL SECTION
========================================= --}}
    @if ($data['testimonials']->count() > 0)

        <section class="py-20 bg-gray-950">

            <div class="max-w-7xl mx-auto px-4">

                {{-- SECTION HEADER --}}
                <div class="text-center mb-14">

                    <p class="primary-text mb-2">
                        Testimonials
                    </p>

                    <h2 class="text-4xl font-bold">
                        What Our Students & Parents Say
                    </h2>

                    <p class="text-gray-400 mt-4">
                        Hear from our happy students, parents, and well-wishers.
                    </p>

                </div>

                {{-- TESTIMONIAL GRID --}}
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">

                    @foreach ($data['testimonials'] as $testimonial)
                        <div
                            class="bg-gray-900 rounded-2xl overflow-hidden shadow-lg hover:scale-105 transition duration-300">

                            {{-- IMAGE / VIDEO --}}
                            <div class="relative">

                                @if ($testimonial->thumbnail_image)
                                    <img src="{{ asset($testimonial->thumbnail_image) }}"
                                        class="w-full h-64 object-cover">
                                @else
                                    <img src="https://via.placeholder.com/500x300" class="w-full h-64 object-cover">
                                @endif

                                {{-- VIDEO BUTTON --}}
                                @if ($testimonial->video_url)
                                    <a href="{{ $testimonial->video_url }}" target="_blank"
                                        class="absolute inset-0 flex items-center justify-center bg-black/40">

                                        <div
                                            class="w-16 h-16 rounded-full bg-white/20 backdrop-blur flex items-center justify-center text-white text-3xl">
                                            ▶
                                        </div>

                                    </a>
                                @endif

                            </div>

                            {{-- CONTENT --}}
                            <div class="p-6">

                                {{-- RATING --}}
                                <div class="flex items-center gap-1 mb-4">

                                    @for ($i = 1; $i <= 5; $i++)
                                        @if ($i <= $testimonial->ratting)
                                            <span class="text-yellow-400 text-lg">★</span>
                                        @else
                                            <span class="text-gray-600 text-lg">★</span>
                                        @endif
                                    @endfor

                                </div>

                                {{-- DESCRIPTION --}}
                                <p class="text-gray-300 leading-7 mb-5">
                                    “{{ Str::limit($testimonial->description, 180) }}”
                                </p>

                                {{-- USER --}}
                                <div class="border-t border-gray-800 pt-4">

                                    <h4 class="text-xl font-semibold">
                                        {{ $testimonial->name }}
                                    </h4>

                                    @if ($testimonial->note)
                                        <p class="text-sm text-gray-400 mt-1">
                                            {{ $testimonial->note }}
                                        </p>
                                    @endif

                                </div>

                            </div>

                        </div>
                    @endforeach

                </div>
            </div>
        </section>
    @endif

    {{-- =========================================
        FAQ
    ========================================= --}}
    <section id="faq" class="py-20 bg-gray-900">

        <div class="max-w-4xl mx-auto px-4">

            <div class="text-center mb-12">

                <h2 class="text-4xl font-bold">
                    Frequently Asked Questions
                </h2>

            </div>

            <div class="space-y-5">

                @foreach ($data['faqs'] as $faq)
                    <div class="card-bg rounded-xl p-5">

                        <button onclick="toggleFAQ({{ $faq->id }})"
                            class="w-full text-left text-lg font-semibold">

                            {{ $faq->question }}

                        </button>

                        <div id="faq{{ $faq->id }}" class="hidden mt-4 text-gray-400">

                            {{ $faq->answer }}

                        </div>

                    </div>
                @endforeach

            </div>

        </div>

    </section>

    {{-- =========================================
        FOOTER
    ========================================= --}}
    <footer class="bg-black py-14">

        <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-3 gap-10">

            <div>

                <h3 class="text-2xl font-bold mb-4">
                    {{ $settings['school_name'] ?? '' }}
                </h3>

                <p class="text-gray-400">
                    {{ $settings['address'] ?? '' }}
                </p>

                <p class="text-gray-400 mt-2">
                    {{ $settings['phone'] ?? '' }}
                </p>

                <p class="text-gray-400">
                    {{ $settings['email'] ?? '' }}
                </p>

            </div>

            <div>

                <h3 class="font-semibold text-xl mb-4">
                    Quick Links
                </h3>

                <ul class="space-y-3 text-gray-400">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#teachers">Teachers</a></li>
                    <li><a href="#events">Events</a></li>
                </ul>

            </div>

            <div>

                <h3 class="font-semibold text-xl mb-4">
                    Social Links
                </h3>

                <div class="flex gap-4">

                    @if (!empty($settings['facebook_link']))
                        <a href="{{ $settings['facebook_link'] }}" target="_blank"
                            class="primary-bg px-4 py-2 rounded-lg">
                            Facebook
                        </a>
                    @endif

                    @if (!empty($settings['youtube_link']))
                        <a href="{{ $settings['youtube_link'] }}" target="_blank"
                            class="primary-bg px-4 py-2 rounded-lg">
                            YouTube
                        </a>
                    @endif

                    @if (!empty($settings['whats_app_link']))
                        <a href="{{ $settings['whats_app_link'] }}" target="_blank"
                            class="primary-bg px-4 py-2 rounded-lg">
                            WhatsApp
                        </a>
                    @endif

                </div>

            </div>

        </div>

        <div class="text-center text-gray-500 mt-12">
            {!! $settings['copyright_text'] ?? 'All Rights Reserved' !!}
        </div>

    </footer>

    {{-- =========================================
        JS
    ========================================= --}}
    <script>
        const menuBtn = document.getElementById('menuBtn');
        const mobileMenu = document.getElementById('mobileMenu');

        menuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        function toggleFAQ(id) {
            document.getElementById('faq' + id).classList.toggle('hidden');
        }
    </script>

</body>

</html>
