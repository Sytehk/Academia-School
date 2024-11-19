{{-- =====================================================
    PROFESSIONAL SAAS FOOTER (THEME-02)
    ===================================================== --}}
<footer id="contact"
    class="relative pt-24 pb-10 overflow-hidden text-white bg-gradient-to-b from-black via-gray-950 to-black">

    {{-- BACKGROUND EFFECT --}}
    <div class="absolute inset-0 opacity-20">
        <div class="absolute w-[500px] h-[500px] bg-purple-600 blur-[120px] rounded-full top-[-100px] left-[-100px]">
        </div>
        <div class="absolute w-[400px] h-[400px] bg-blue-600 blur-[120px] rounded-full bottom-[-120px] right-[-80px]">
        </div>
    </div>

    <div class="relative grid gap-10 px-4 mx-auto max-w-7xl md:grid-cols-4">

        {{-- =====================================================
            BRAND INFO
        ====================================================== --}}
        <div data-aos="fade-up">
            @if (!empty($instituteImage?->header_logo_light_theme))
                <div class="p-1.5 rounded-xl shadow-xl hover:scale-105 transition-all duration-300">
                    <a href="{{ url('/') }}">
                        <img src="{{ asset('storage/institute_image_settings/' . $instituteImage->header_logo_light_theme) }}"
                            alt="" class="h-10 md:h-12 w-auto max-w-[180px] object-contain rounded-lg">
                    </a>
                </div>
            @else
                <div class="p-1.5 rounded-xl shadow-xl hover:scale-105 transition-all duration-300">
                    <a href="{{ url('/') }}">
                        <img src="{{ asset('assets/media/logos/default.png') }}" alt=""
                            class="h-10 md:h-12 w-auto max-w-[180px] object-contain rounded-lg">
                    </a>
                </div>
            @endif


            {{-- LEGAL --}}
            <div>
                <h3 class="mb-4 text-3xl font-extrabold">
                    {{ $settings['site_title'] ?? ($settings['school_name'] ?? 'Institute Pro') }}
                </h3>
            </div>

            <p class="mb-5 leading-7 text-gray-400">
                Modern SaaS-based institute management system for schools, colleges, and training centers.
            </p>

            {{-- SOCIAL --}}
            <div class="flex gap-4 text-xl">

                @if (!empty($settings['facebook_link']))
                    <a href="{{ $settings['facebook_link'] }}" class="transition hover:text-blue-500">
                        <i class="fab fa-facebook"></i>
                    </a>
                @endif

                @if (!empty($settings['youtube_link']))
                    <a href="{{ $settings['youtube_link'] }}" class="transition hover:text-red-500">
                        <i class="fab fa-youtube"></i>
                    </a>
                @endif

                @if (!empty($settings['whats_app_link']))
                    <a href="{{ $settings['whats_app_link'] }}" class="transition hover:text-green-500">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                @endif

                @if (!empty($settings['linkedin_link']))
                    <a href="{{ $settings['linkedin_link'] }}" class="transition hover:text-blue-400">
                        <i class="fab fa-linkedin"></i>
                    </a>
                @endif
            </div>
        </div>

        {{-- =====================================================
        Contact Info
        ====================================================== --}}
        <div data-aos="fade-up" data-aos-delay="300">

            <h3 class="flex items-center gap-2 mb-5 text-xl font-bold">

                <span class="flex items-center justify-center w-8 h-8 text-sm rounded-lg bg-white/10">
                    <i class="fas fa-address-book"></i>
                </span>

                Contact Info
            </h3>

            {{-- CONTACT DETAILS --}}
            <div class="mt-5 space-y-3 text-sm text-gray-200">

                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-8 h-8 text-xs rounded-lg bg-white/10">
                        <i class="fas fa-phone-alt"></i>
                    </span>

                    <p>{{ $settings['phone'] ?? '' }}</p>
                </div>

                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-8 h-8 text-xs rounded-lg bg-white/10">
                        <i class="fas fa-envelope"></i>
                    </span>

                    <p>{{ $settings['email'] ?? '' }}</p>
                </div>

                <div class="flex items-start gap-3">
                    <span class="flex items-center justify-center w-8 h-8 mt-1 text-xs rounded-lg bg-white/10">
                        <i class="fas fa-map-marker-alt"></i>
                    </span>

                    <p>{{ $settings['address'] ?? '' }}</p>
                </div>

            </div>


        </div>

        {{-- =====================================================
            QUICK LINKS
        ====================================================== --}}
        <div data-aos="fade-up" data-aos-delay="100">

            <h3 class="mb-5 text-xl font-bold">Quick Links</h3>

            <ul class="space-y-3 text-gray-400">
                <li><a href="#home" class="transition hover:text-white">Home</a></li>

                @if ($settings['admission_display_status'] == 'yes')
                    <li><a href="{{ route('admission-form-page') }}" class="hover:text-white">Admission</a></li>
                @endif

                @if ($settings['exam_result_status'] == 'yes')
                    <li><a href="{{ route('result-page') }}" class="hover:text-white">Exam Results</a></li>
                @endif
                <li><a href="{{ route('academic-images') }}" class="hover:text-white">Galleries</a></li>
            </ul>
        </div>

        {{-- =====================================================
            STUDENT PORTAL
        ====================================================== --}}
        <div data-aos="fade-up" data-aos-delay="200">
            <h3 class="mb-5 text-xl font-bold">Legal</h3>

            <ul class="space-y-3 text-gray-400">
                <li><a href="{{ route('privacy-policy') }}" class="transition hover:text-white">Privacy Policy</a></li>
                <li><a href="{{ route('terms-conditions') }}" class="transition hover:text-white">Terms &
                        Conditions</a></li>
                <li><a href="{{ route('refund-policy') }}" class="transition hover:text-white">Refund Policy</a></li>
                <li><a href="{{ route('cookies-policy') }}" class="transition hover:text-white">Cookies Policy</a></li>
            </ul>

            @if (!empty($settings['play_store_link']) || !empty($settings['app_store_link']))
                {{-- DOWNLOAD APP --}}
                <div class="mt-8">
                    <h4 class="flex items-center gap-2 mb-4 font-semibold text-white">
                        <span class="flex items-center justify-center w-8 h-8 text-xs rounded-lg bg-white/10">
                            <i class="fas fa-mobile-alt"></i>
                        </span>
                        Download App
                    </h4>

                    <div class="space-y-3">
                        @if (!empty($settings['play_store_link']))
                            {{-- ANDROID --}}
                            <a href="{{ $settings['play_store_link'] }}"
                                class="flex items-center gap-3 p-3 text-sm transition duration-300 bg-white/10 rounded-xl hover:bg-white/20">

                                <span
                                    class="flex items-center justify-center text-sm text-green-400 rounded-lg w-9 h-9 bg-green-500/20">
                                    <i class="fab fa-google-play"></i>
                                </span>

                                <div>
                                    <div class="text-xs text-gray-300">
                                        Get it on
                                    </div>

                                    <div class="font-semibold text-white">
                                        Android App
                                    </div>
                                </div>

                            </a>
                        @endif

                        @if (!empty($settings['app_store_link']))
                            {{-- IOS --}}
                            <a href="{{ $settings['app_store_link'] }}"
                                class="flex items-center gap-3 p-3 text-sm transition duration-300 bg-white/10 rounded-xl hover:bg-white/20">

                                <span
                                    class="flex items-center justify-center text-sm text-white rounded-lg w-9 h-9 bg-white/20">
                                    <i class="fab fa-apple"></i>
                                </span>

                                <div>
                                    <div class="text-xs text-gray-300">
                                        Download on the
                                    </div>

                                    <div class="font-semibold text-white">
                                        iOS App
                                    </div>
                                </div>

                            </a>
                        @endif
                    </div>

                </div>
            @endif
        </div>
    </div>

    {{-- =====================================================
        BOTTOM SECTION
    ====================================================== --}}
    <div class="px-4 mx-auto mt-5 border-t max-w-7xl border-white/10">
        {{-- COPYRIGHT --}}
        <div class="mt-5 text-sm text-center text-gray-500">
            {!! $settings['copyright_text'] ?? '© 2026 All Rights Reserved - Institute SaaS System' !!}
        </div>
    </div>
</footer>

{{-- =====================================================
        WHATSAPP
    ====================================================== --}}
@if (!empty($settings['whats_app_link']))
    <a href="{{ $settings['whats_app_link'] }}" target="_blank"
        class="fixed z-50 flex items-center justify-center w-16 h-16 text-3xl text-white bg-green-500 rounded-full shadow-2xl bottom-6 right-6 animate-bounce">
        <i class="fab fa-whatsapp"></i>
    </a>
@endif
