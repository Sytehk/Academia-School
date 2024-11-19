{{-- =========================================================
    PROFESSIONAL 3D TEACHERS SECTION
    ========================================================= --}}

@if ($data['teachers'] && count($data['teachers']) > 0)
    <section id="teachers" class="relative py-28 bg-gradient-to-b from-slate-50 to-white overflow-hidden">

        <div class="max-w-7xl mx-auto px-4">

            {{-- TITLE --}}
            <div class="text-center mb-20" data-aos="fade-up">

                <span class="text-primary uppercase tracking-[5px] font-semibold">
                    Our Faculty
                </span>

                <h2 class="text-5xl font-black mt-4">
                    Meet Our Professional Teachers
                </h2>

                <p class="text-gray-500 mt-5 max-w-2xl mx-auto leading-8">
                    Dedicated educators helping students achieve excellence and success.
                </p>

            </div>

            {{-- TEACHERS GRID --}}
            <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-10">

                @foreach ($data['teachers'] as $teacher)
                    <div class="group relative" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 100 }}">

                        {{-- GLOW --}}
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-purple-500 to-pink-500 rounded-[35px] blur-xl opacity-0 group-hover:opacity-40 transition duration-500">
                        </div>

                        <div
                            class="relative bg-white rounded-[35px] overflow-hidden shadow-xl border border-gray-100 hover:-translate-y-5 transition duration-500">

                            {{-- IMAGE --}}
                            <div class="relative overflow-hidden">

                                <img src="{{ $teacher->user?->image && file_exists(public_path('storage/users/' . $teacher->user?->image))
                                    ? asset('storage/users/' . $teacher->user?->image)
                                    : asset('images/user.png') }}"
                                    class="w-full h-[350px] object-cover group-hover:scale-110 transition duration-700">
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent">
                                </div>

                                {{-- SOCIAL --}}
                                <div
                                    class="absolute bottom-5 left-0 right-0 flex justify-center gap-4 opacity-0 group-hover:opacity-100 transition duration-500">

                                    <a href="#"
                                        class="w-11 h-11 rounded-full bg-white text-black flex items-center justify-center hover:bg-primary hover:text-white transition">

                                        <i class="fab fa-facebook-f"></i>

                                    </a>

                                    <a href="#"
                                        class="w-11 h-11 rounded-full bg-white text-black flex items-center justify-center hover:bg-primary hover:text-white transition">

                                        <i class="fab fa-linkedin-in"></i>

                                    </a>

                                </div>

                            </div>

                            {{-- CONTENT --}}
                            <div class="p-8 text-center">

                                <h3 class="text-2xl font-bold mb-2">
                                    {{ $teacher->user?->name }}
                                </h3>

                                <p class="text-primary font-semibold mb-4">
                                    {{ $teacher->designation ?? 'Teacher' }}
                                </p>

                            </div>

                        </div>

                    </div>
                @endforeach

            </div>

        </div>

    </section>

@endif
