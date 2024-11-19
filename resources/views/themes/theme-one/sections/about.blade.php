{{-- =====================================================
        WHY CHOOSE US
    ====================================================== --}}
@if ($data['whyChooseUs']->count() > 0)

    <section class="py-24 bg-white relative">

        <div class="max-w-7xl mx-auto px-4">

            <div class="text-center mb-16" data-aos="fade-up">

                <h2 class="section-title bg-gradient-to-r from-purple-600 bg-clip-text text-transparent">
                    Why Choose Us
                </h2>

                <p class="text-gray-500 mt-4">
                    Professional Education Platform
                </p>

            </div>

            <div class="grid md:grid-cols-4 gap-8">
                @foreach ($data['whyChooseUs'] as $item)
                    <div class="theme-card bg-white rounded-3xl p-10 shadow-xl text-center border" data-aos="zoom-in"
                        data-aos-delay="{{ $loop->iteration * 100 }}">

                        <div
                            class="w-24 h-24 rounded-full bg-primary/10 border border-primary/20 overflow-hidden flex items-center justify-center mx-auto shadow-xl">

                            @if (!empty($item->icon))
                                <img src="{{ asset('storage/why_choose_us/' . $item->icon) }}"
                                    alt="{{ $item->title ?? 'Icon' }}" class="w-full h-full object-cover">
                            @else
                                <img src="{{ asset('images/no-image.png') }}" alt="No Image"
                                    class="w-full h-full object-cover">
                            @endif

                        </div>

                        <h3 class="text-2xl font-bold mb-4">
                            {{ $item->title }}
                        </h3>

                        <p class="text-gray-600 leading-7">
                            {{ $item->description }}
                        </p>

                    </div>
                @endforeach

            </div>

        </div>

    </section>

@endif

{{-- =====================================================
        ABOUT
    ====================================================== --}}
@if ($data['about'])
    <section id="about" class="py-24 bg-gray-50">

        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center px-4">

            <div data-aos="fade-right">

                <img src="{{ asset('storage/about_us/' . $data['about']->image) }}"
                    class="rounded-[40px] shadow-2xl w-full">

            </div>

            <div data-aos="fade-left">

                <span class="text-primary font-semibold uppercase tracking-widest">
                    About Us
                </span>

                <h2 class="section-title mt-4 mb-6">
                    {{ $data['about']->title }}
                </h2>

                <div class="text-gray-600 leading-8">
                    {!! $data['about']->description !!}
                </div>

            </div>

        </div>

    </section>
@endif
