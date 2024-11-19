{{-- =========================================
    FINAL FAQ - SMOOTH ANIMATED (PRO LEVEL)
========================================= --}}

@if ($data['faqs'] && count($data['faqs']) > 0)
    <section id="faq"
        class="relative py-28 bg-gradient-to-b from-slate-950 via-slate-900 to-black text-white overflow-hidden">

        <div class="max-w-7xl mx-auto px-4 relative z-10">

            {{-- TITLE --}}
            <div class="text-center mb-14">
                <h2 class="text-5xl font-black">Frequently Asked Questions</h2>
            </div>

            {{-- FAQ ITEMS --}}
            <div class="space-y-5">

                @foreach ($data['faqs'] as $faq)
                    <div class="faq-item bg-white/5 border border-white/10 rounded-2xl overflow-hidden">

                        {{-- QUESTION --}}
                        <button onclick="toggleFAQ({{ $faq->id }})"
                            class="w-full flex justify-between items-center p-6 text-left">

                            <span class="text-lg font-semibold">
                                {{ $faq->question }}
                            </span>

                            <span id="icon{{ $faq->id }}" class="text-xl transition">
                                +
                            </span>

                        </button>

                        {{-- ANSWER (IMPORTANT FIX) --}}
                        <div id="faq{{ $faq->id }}"
                            class="faq-answer px-6 pb-0 max-h-0 overflow-hidden transition-all duration-500 ease-in-out">

                            <div class="py-4 text-slate-300 leading-7">
                                {{ $faq->answer }}
                            </div>

                        </div>

                    </div>
                @endforeach

            </div>

        </div>
    </section>

@endif
