{{-- =========================================================
    PROFESSIONAL EVENTS SECTION
   ========================================================= --}}

@if ($data['events'] && count($data['events']) > 0)
    <section id="events" class="relative py-28 bg-gradient-to-b from-white to-slate-100 overflow-hidden">

        <div class="max-w-7xl mx-auto px-4">

            {{-- TITLE --}}
            <div class="text-center mb-20" data-aos="fade-up">

                <span class="text-primary uppercase tracking-[5px] font-semibold">
                    Upcoming Activities
                </span>

                <h2 class="text-5xl font-black mt-4">
                    School Events & Programs
                </h2>

            </div>

            {{-- EVENTS --}}
            <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-10">

                @foreach ($data['events'] as $event)
                    <div class="group relative" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 100 }}">

                        <div
                            class="absolute inset-0 bg-gradient-to-br from-orange-500 to-red-500 rounded-[35px] blur-xl opacity-0 group-hover:opacity-40 transition duration-500">
                        </div>

                        <div
                            class="relative bg-white rounded-[35px] overflow-hidden shadow-2xl hover:-translate-y-4 transition duration-500">

                            {{-- IMAGE --}}
                            <div class="overflow-hidden relative">

                                <img src="{{ asset('storage/events/' . $event->image) }}"
                                    class="w-full h-72 object-cover group-hover:scale-110 transition duration-700">

                                {{-- DATE --}}
                                <div
                                    class="absolute top-5 left-5 bg-white text-black px-5 py-4 rounded-2xl shadow-xl text-center">

                                    <h4 class="text-3xl font-black">
                                        {{ \Carbon\Carbon::parse($event->start_date)->format('d') }}
                                    </h4>

                                    <p class="text-sm font-semibold">
                                        {{ \Carbon\Carbon::parse($event->start_date)->format('M') }}
                                    </p>

                                </div>

                            </div>

                            {{-- CONTENT --}}
                            <div class="p-8">

                                <div class="flex items-center gap-3 text-sm text-gray-500 mb-5">

                                    <span>
                                        <i class="fa fa-location-dot text-primary"></i>
                                        {{ $event->location }}
                                    </span>

                                </div>

                                <h3 class="text-3xl font-bold mb-5">
                                    {{ $event->name }}
                                </h3>

                                <p class="text-gray-600 leading-8 mb-7">
                                    {{ Str::limit(strip_tags($event->details), 120) }}
                                </p>

                                <a href="{{ route('event-details', $event->id) }}"
                                    class="inline-flex items-center gap-3 text-primary font-semibold">

                                    Explore Event
                                    <i class="fa fa-arrow-right"></i>

                                </a>

                            </div>

                        </div>

                    </div>
                @endforeach

            </div>

        </div>
    </section>
@endif
