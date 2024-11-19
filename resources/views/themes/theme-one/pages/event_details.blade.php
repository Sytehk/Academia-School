@extends('themes.theme-one.layouts.app')

@section('content')
    <style>
        /* =====================================================
                            EVENT DETAILS PAGE
        ===================================================== */

        .event-wrapper {

            background: white;

            border-radius: 30px;

            overflow: hidden;

            box-shadow: 0 15px 50px rgba(0, 0, 0, .08);
        }

        .event-image {

            width: 100%;

            height: 550px;

            object-fit: cover;
        }

        .event-content h1,
        .event-content h2,
        .event-content h3,
        .event-content h4,
        .event-content h5,
        .event-content h6 {

            color: #0f172a !important;

            font-weight: 700;
        }

        .event-content,
        .event-content * {

            color: #334155 !important;
        }

        .event-content a {

            color: #2563eb !important;
        }

        .event-meta {

            display: flex;

            flex-wrap: wrap;

            gap: 20px;

            margin-top: 20px;

            margin-bottom: 35px;
        }

        .event-meta-box {

            display: flex;

            align-items: center;

            gap: 12px;

            padding: 14px 20px;

            border-radius: 18px;

            background: #f8fafc;

            font-weight: 600;

            color: #334155;
        }

        .event-meta-box i {

            color: var(--primary-color, #2563eb);
        }

        /* =====================================================
                            DARK MODE
        ===================================================== */

        html[data-bs-theme="dark"] .event-wrapper {

            background: #0f172a !important;
        }

        html[data-bs-theme="dark"] .event-content,
        html[data-bs-theme="dark"] .event-content * {

            color: #f8fafc !important;
        }

        html[data-bs-theme="dark"] .event-content a {

            color: #60a5fa !important;
        }

        html[data-bs-theme="dark"] .event-meta-box {

            background: #1e293b !important;

            color: white !important;
        }
    </style>

    <section class="relative py-20 bg-gradient-to-br from-slate-50 via-white to-slate-100 overflow-hidden">

        <div class="max-w-7xl mx-auto px-4">

            {{-- EVENT CARD --}}
            <div class="event-wrapper">

                {{-- IMAGE --}}
                <div class="relative">

                    <img src="{{ asset('storage/events/' . $data->image) }}" alt="{{ $data->name }}" class="event-image">

                    {{-- DATE --}}
                    <div class="absolute bottom-8 left-8 bg-white rounded-3xl px-8 py-5 shadow-2xl text-center">

                        <h2 class="text-5xl font-black text-slate-900 leading-none">
                            {{ \Carbon\Carbon::parse($data->start_date)->format('d') }}
                        </h2>

                        <p class="uppercase font-bold text-primary tracking-widest mt-2">
                            {{ \Carbon\Carbon::parse($data->start_date)->format('M Y') }}
                        </p>

                    </div>

                </div>

                {{-- CONTENT --}}
                <div class="p-8 md:p-14">

                    {{-- TITLE --}}
                    <h1 class="text-4xl md:text-5xl font-black text-slate-900 mb-4">
                        {{ $data->name }}
                    </h1>

                    {{-- META --}}
                    <div class="event-meta">

                        <div class="event-meta-box">

                            <i class="fa-solid fa-location-dot"></i>

                            <span>
                                {{ $data->location ?? 'Location Not Available' }}
                            </span>

                        </div>

                        <div class="event-meta-box">

                            <i class="fa-solid fa-calendar-days"></i>

                            <span>
                                {{ \Carbon\Carbon::parse($data->start_date)->format('d M Y') }}
                            </span>

                        </div>

                        @if (!empty($data->end_date))
                            <div class="event-meta-box">

                                <i class="fa-solid fa-clock"></i>

                                <span>
                                    Ends:
                                    {{ \Carbon\Carbon::parse($data->end_date)->format('d M Y') }}
                                </span>

                            </div>
                        @endif

                    </div>

                    {{-- DETAILS --}}
                    <div class="event-content leading-9 text-lg">

                        {!! $data->details !!}

                    </div>

                </div>

            </div>

        </div>

    </section>
@endsection
