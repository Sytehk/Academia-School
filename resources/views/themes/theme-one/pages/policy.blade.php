@extends('themes.theme-one.layouts.app')

@section('content')
    <style>
        /* ========================================
                   POLICY PAGE
                ======================================== */

        .policy-wrapper {
            background: white;
        }

        /* CONTENT */

        .policy-content,
        .policy-content * {

            color: #1e293b !important;
        }

        /* HEADING */

        .policy-content h1,
        .policy-content h2,
        .policy-content h3,
        .policy-content h4,
        .policy-content h5,
        .policy-content h6 {

            color: #0f172a !important;

            font-weight: 700;
        }

        /* LINK */

        .policy-content a {

            color: #2563eb !important;
        }

        /* EMPTY */

        .empty-policy {

            text-align: center;

            padding: 80px 20px;
        }

        .empty-icon {

            width: 120px;
            height: 120px;

            margin: auto;

            border-radius: 999px;

            background: #f1f5f9;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 60px;

            margin-bottom: 24px;
        }

        .empty-policy h2 {

            font-size: 34px;

            font-weight: 800;

            color: #0f172a;

            margin-bottom: 14px;
        }

        .empty-policy p {

            color: #64748b;

            font-size: 18px;
        }

        /* ========================================
                   DARK MODE
                ======================================== */

        html[data-bs-theme="dark"] .policy-wrapper {

            background: #0f172a !important;
        }

        /* ALL TEXT WHITE */

        html[data-bs-theme="dark"] .policy-content,
        html[data-bs-theme="dark"] .policy-content * {

            color: #ffffff !important;
        }

        /* LINKS */

        html[data-bs-theme="dark"] .policy-content a {

            color: #60a5fa !important;
        }

        /* EMPTY */

        html[data-bs-theme="dark"] .empty-policy h2 {

            color: #ffffff !important;
        }

        html[data-bs-theme="dark"] .empty-policy p {

            color: #cbd5e1 !important;
        }

        html[data-bs-theme="dark"] .empty-icon {

            background: #1e293b !important;
        }
    </style>

    <section class="bg-gradient-to-br from-slate-50 via-white to-slate-100 py-14">

        <div class="max-w-5xl mx-auto px-4">

            {{-- PAGE CARD --}}
            {{-- CONTENT --}}
            <div class="p-8 md:p-12 bg-white policy-wrapper">

                @if (!empty($data?->description))
                    <div class="policy-content">

                        {!! $data->description !!}

                    </div>
                @else
                    <div class="empty-policy">

                        <div class="empty-icon">
                            📄
                        </div>

                        <h2>
                            No Data Found
                        </h2>

                        <p>
                            This policy page is currently empty.
                            Please check again later.
                        </p>

                    </div>
                @endif

            </div>
        </div>
    </section>
@endsection
