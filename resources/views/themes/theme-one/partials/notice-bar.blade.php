{{-- =====================================================
        TOP NOTICE BAR
====================================================== --}}
@if (!empty($settings['header_notice']))
    <div class="bg-[#803ef2] text-white overflow-hidden relative z-50">

        <div class="max-w-7xl mx-auto flex items-center">

            <div class="bg-black/20 px-6 py-3 font-bold whitespace-nowrap text-white">

                Latest Notice

            </div>


            <div class="flex-1 marquee py-3">

                <div class="marquee-track">

                    <span class="mx-10 text-white">

                        {{ $settings['header_notice'] }}

                    </span>

                </div>

            </div>

        </div>

    </div>
@endif
