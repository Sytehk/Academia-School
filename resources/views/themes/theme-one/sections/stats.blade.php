{{-- =========================================================
    PROFESSIONAL 3D STATS SECTION
========================================================= --}}

<section class="relative py-28 overflow-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white">

    {{-- GLOW SHAPES --}}
    <div class="absolute top-0 left-0 w-72 h-72 bg-purple-600/20 blur-[120px] rounded-full"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-pink-500/20 blur-[150px] rounded-full"></div>

    {{-- GRID --}}
    <div class="absolute inset-0 opacity-10"
        style="background-image: linear-gradient(#ffffff20 1px, transparent 1px), linear-gradient(to right, #ffffff20 1px, transparent 1px); background-size: 40px 40px;">
    </div>

    <div class="max-w-7xl mx-auto px-4 relative z-10">

        {{-- TITLE --}}
        <div class="text-center mb-20" data-aos="fade-up">

            <span
                class="inline-block px-6 py-2 rounded-full bg-white/10 border border-white/10 backdrop-blur text-sm uppercase tracking-widest mb-5">

                School Achievements

            </span>

            <h2 class="text-5xl md:text-6xl font-black leading-tight">

                Trusted By
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-500">
                    Thousands
                </span>

            </h2>

            <p class="text-slate-300 mt-6 max-w-2xl mx-auto leading-8">
                We provide modern education systems, professional teachers,
                quality courses and unforgettable learning experiences.
            </p>

        </div>

        {{-- STATS --}}
        <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-8">

            {{-- TEACHERS --}}
            <div class="group relative" data-aos="zoom-in">

                <div
                    class="absolute inset-0 bg-gradient-to-br from-purple-600 to-pink-500 rounded-[35px] blur-xl opacity-40 group-hover:opacity-70 transition duration-500">
                </div>

                <div
                    class="relative h-full backdrop-blur-xl bg-white/10 border border-white/10 rounded-[35px] p-10 overflow-hidden hover:-translate-y-4 transition duration-500">

                    {{-- ICON --}}
                    <div
                        class="w-24 h-24 rounded-3xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-4xl shadow-2xl mb-8">

                        <i class="fa-solid fa-user-graduate"></i>

                    </div>

                    {{-- COUNT --}}
                    <h3 class="text-6xl font-black mb-3">

                        {{ $data['teachers']->count() }}+

                    </h3>

                    <h4 class="text-2xl font-bold mb-4">
                        Expert Teachers
                    </h4>

                    <p class="text-slate-300 leading-7">
                        Highly qualified teachers with real classroom experience.
                    </p>

                    {{-- DECORATION --}}
                    <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-white/5 rounded-full">
                    </div>

                </div>

            </div>

            {{-- COURSES --}}
            <div class="group relative" data-aos="zoom-in" data-aos-delay="100">

                <div
                    class="absolute inset-0 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-[35px] blur-xl opacity-40 group-hover:opacity-70 transition duration-500">
                </div>

                <div
                    class="relative h-full backdrop-blur-xl bg-white/10 border border-white/10 rounded-[35px] p-10 overflow-hidden hover:-translate-y-4 transition duration-500">

                    <div
                        class="w-24 h-24 rounded-3xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-4xl shadow-2xl mb-8">

                        <i class="fa-solid fa-book-open-reader"></i>

                    </div>

                    <h3 class="text-6xl font-black mb-3">

                        {{ (int) ($data['student_count'] ?? 0) }}+

                    </h3>

                    <h4 class="text-2xl font-bold mb-4">
                        Total Students
                    </h4>

                    <p class="text-slate-300 leading-7">
                        Students currently enrolled across classes and sections.
                    </p>

                    <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-white/5 rounded-full">
                    </div>

                </div>

            </div>

            {{-- EVENTS --}}
            <div class="group relative" data-aos="zoom-in" data-aos-delay="200">

                <div
                    class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-green-600 rounded-[35px] blur-xl opacity-40 group-hover:opacity-70 transition duration-500">
                </div>

                <div
                    class="relative h-full backdrop-blur-xl bg-white/10 border border-white/10 rounded-[35px] p-10 overflow-hidden hover:-translate-y-4 transition duration-500">

                    <div
                        class="w-24 h-24 rounded-3xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center text-4xl shadow-2xl mb-8">

                        <i class="fa-solid fa-calendar-days"></i>

                    </div>

                    <h3 class="text-6xl font-black mb-3">

                        {{ $data['events']->count() }}+

                    </h3>

                    <h4 class="text-2xl font-bold mb-4">
                        School Events
                    </h4>

                    <p class="text-slate-300 leading-7">
                        Educational, cultural and technology-driven campus events.
                    </p>

                    <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-white/5 rounded-full">
                    </div>

                </div>

            </div>

            {{-- TESTIMONIALS --}}
            <div class="group relative" data-aos="zoom-in" data-aos-delay="300">

                <div
                    class="absolute inset-0 bg-gradient-to-br from-orange-500 to-red-500 rounded-[35px] blur-xl opacity-40 group-hover:opacity-70 transition duration-500">
                </div>

                <div
                    class="relative h-full backdrop-blur-xl bg-white/10 border border-white/10 rounded-[35px] p-10 overflow-hidden hover:-translate-y-4 transition duration-500">

                    <div
                        class="w-24 h-24 rounded-3xl bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center text-4xl shadow-2xl mb-8">

                        <i class="fa-solid fa-star"></i>

                    </div>

                    <h3 class="text-6xl font-black mb-3">

                        {{ $data['testimonials']->count() }}+

                    </h3>

                    <h4 class="text-2xl font-bold mb-4">
                        Happy Reviews
                    </h4>

                    <p class="text-slate-300 leading-7">
                        Parents and students love our quality education platform.
                    </p>

                    <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-white/5 rounded-full">
                    </div>

                </div>

            </div>

        </div>

    </div>

</section>
