{{-- =====================================================
        COURSES
    ====================================================== --}}
@if ($data['courses']->count() > 0)

    <section id="courses" class="py-24 bg-white">

        <div class="max-w-7xl mx-auto px-4">

            <div class="text-center mb-16" data-aos="fade-up">

                <h2 class="section-title">
                    Popular Courses
                </h2>

            </div>

            <div class="grid md:grid-cols-3 gap-10">

                @foreach ($data['courses'] as $course)
                    <div class="theme-card bg-white rounded-[30px] overflow-hidden shadow-xl" data-aos="fade-up"
                        data-aos-delay="{{ $loop->iteration * 100 }}">

                        <div class="overflow-hidden">

                            <img src="{{ asset('storage/courses/' . $course->image) }}"
                                class="w-full h-72 object-cover hover:scale-110 transition duration-700">

                        </div>

                        <div class="p-8">

                            <h3 class="text-2xl font-bold mb-4">
                                {{ $course->title }}
                            </h3>

                            <p class="text-gray-600 leading-7">
                                {{ Str::limit($course->description, 120) }}
                            </p>

                            <button class="btn-primary mt-6 px-6 py-3 rounded-full font-semibold">

                                Apply Now

                            </button>

                        </div>

                    </div>
                @endforeach

            </div>

        </div>

    </section>

@endif



{{-- =========================================================
    PROFESSIONAL COURSES SECTION
    ========================================================= --}}

<section id="courses" class="relative py-28 bg-slate-950 overflow-hidden text-white">

    <div class="absolute inset-0 opacity-10"
        style="background-image: radial-gradient(circle at center, #ffffff 1px, transparent 1px); background-size: 30px 30px;">
    </div>

    <div class="max-w-7xl mx-auto px-4 relative z-10">

        {{-- TITLE --}}
        <div class="text-center mb-20" data-aos="fade-up">

            <span class="px-5 py-2 rounded-full bg-white/10 border border-white/10 text-sm uppercase tracking-widest">

                Academic Courses

            </span>

            <h2 class="text-5xl font-black mt-6">

                Explore Our
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">
                    Modern Courses
                </span>

            </h2>

        </div>

        {{-- COURSES --}}
        <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-10">

            @foreach ($data['courses'] as $course)
                <div class="group relative" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 100 }}">

                    <div
                        class="absolute inset-0 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-[35px] blur-xl opacity-20 group-hover:opacity-50 transition duration-500">
                    </div>

                    <div
                        class="relative bg-white/10 border border-white/10 backdrop-blur-xl rounded-[35px] overflow-hidden hover:-translate-y-5 transition duration-500">

                        {{-- IMAGE --}}
                        <div class="overflow-hidden relative">

                            <img src="{{ asset('storage/courses/' . $course->image) }}"
                                class="w-full h-72 object-cover group-hover:scale-110 transition duration-700">

                            <div class="absolute top-5 left-5 bg-primary px-4 py-2 rounded-full text-sm font-semibold">

                                {{ ucfirst($course->payment_type) }}

                            </div>

                        </div>

                        {{-- CONTENT --}}
                        <div class="p-8">

                            <h3 class="text-3xl font-bold mb-4">
                                {{ $course->title }}
                            </h3>

                            <p class="text-slate-300 leading-8 mb-6">
                                {{ Str::limit(strip_tags($course->description), 130) }}
                            </p>

                            {{-- STATS --}}
                            <div class="grid grid-cols-3 gap-4 mb-8 text-center">

                                <div class="bg-white/10 rounded-2xl py-4">

                                    <h4 class="font-bold text-xl">
                                        {{ $course->total_classes }}
                                    </h4>

                                    <p class="text-xs text-slate-300">
                                        Classes
                                    </p>

                                </div>

                                <div class="bg-white/10 rounded-2xl py-4">

                                    <h4 class="font-bold text-xl">
                                        {{ $course->total_exams }}
                                    </h4>

                                    <p class="text-xs text-slate-300">
                                        Exams
                                    </p>

                                </div>

                                <div class="bg-white/10 rounded-2xl py-4">

                                    <h4 class="font-bold text-xl">
                                        {{ $course->total_notes }}
                                    </h4>

                                    <p class="text-xs text-slate-300">
                                        Notes
                                    </p>

                                </div>

                            </div>

                            {{-- BUTTON --}}
                            <a href="#"
                                class="inline-flex items-center gap-3 px-7 py-4 rounded-full bg-gradient-to-r from-cyan-500 to-blue-600 font-semibold hover:scale-105 transition">

                                Enroll Now
                                <i class="fa fa-arrow-right"></i>

                            </a>

                        </div>

                    </div>

                </div>
            @endforeach

        </div>

    </div>

</section>
