{{-- =========================================================
    PROFESSIONAL TESTIMONIALS SECTION
========================================================= --}}

@if ($data['testimonials'] && count($data['testimonials']) > 0)
    <section id="testimonials" class="relative py-28 overflow-hidden bg-slate-950 text-white">

        <div class="absolute top-0 left-0 w-full h-full opacity-10"
            style="background-image: radial-gradient(circle at center, white 1px, transparent 1px); background-size: 25px 25px;">
        </div>

        <div class="max-w-7xl mx-auto px-4 relative z-10">

            {{-- TITLE --}}
            <div class="text-center mb-20" data-aos="fade-up">

                <span
                    class="px-5 py-2 rounded-full bg-white/10 border border-white/10 text-sm uppercase tracking-widest">

                    Testimonials

                </span>

                <h2 class="text-5xl font-black mt-6">

                    What Students &
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-orange-500">
                        Parents Say
                    </span>

                </h2>

            </div>

            {{-- TESTIMONIAL GRID --}}
            <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-10">
                @foreach ($data['testimonials'] as $testimonial)
                    <div class="group relative" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 100 }}">

                        <div
                            class="absolute inset-0 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-[35px] blur-xl opacity-0 group-hover:opacity-40 transition duration-500">
                        </div>

                        <div
                            class="relative bg-white/10 border border-white/10 backdrop-blur-xl rounded-[35px] p-10 hover:-translate-y-4 transition duration-500">

                            {{-- QUOTE --}}
                            <div class="text-6xl text-yellow-400 mb-6">
                                ❝
                            </div>

                            {{-- REVIEW --}}
                            <p class="text-slate-300 leading-8 mb-8 italic">
                                "{{ Str::limit($testimonial->description, 180) }}"
                            </p>

                            {{-- RATING --}}
                            <div class="flex gap-1 text-yellow-400 text-xl mb-8">

                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= $testimonial->ratting)
                                        ★
                                    @else
                                        ☆
                                    @endif
                                @endfor

                            </div>

                            {{-- USER --}}
                            <div class="flex items-center gap-5">
                                <img src="{{ $testimonial->thumbnail_image &&
                                file_exists(public_path('storage/testimonials/' . $testimonial->thumbnail_image))
                                    ? asset('storage/testimonials/' . $testimonial->thumbnail_image)
                                    : asset('images/user.png') }}"
                                    class="w-20 h-20 rounded-full object-cover border-4 border-white/20">

                                <div>
                                    <h4 class="text-2xl font-bold">
                                        {{ $testimonial->name }}
                                    </h4>

                                    <p class="text-slate-400">
                                        {{ $testimonial->note }}
                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>
                @endforeach

            </div>

        </div>

    </section>
@endif
