<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') &middot; {{ config('app.name', 'Installation') }}</title>

    <link rel="icon" type="image/png" href="{{ get_logo() }}">

    {{-- Offline-safe: local Tailwind build + local Alpine build, no CDN --}}
    @include('partials.local-assets.head', ['skipTailwind' => false])
    @include('partials.local-assets.scripts', ['alpine' => true])

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>

<body class="min-h-screen antialiased text-slate-800">

    <div class="min-h-screen flex flex-col">

        <header class="border-b border-slate-200 bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ get_logo() }}" alt="Logo" class="h-9 w-9 rounded-lg object-cover">
                    <div>
                        <div class="text-sm font-semibold text-slate-900">{{ config('app.name', 'AceCademy') }}</div>
                        <div class="text-xs text-slate-500">{{ _lang('Installation Wizard') }}</div>
                    </div>
                </div>
                <div class="text-xs font-medium text-slate-400">v{{ config('system.version', config('install.app_version', '1.0.0')) }}</div>
            </div>

            @php
                $installStepKey = trim($__env->yieldContent('step_key', ''));
            @endphp

            @if ($installStepKey)
                <div class="max-w-4xl mx-auto px-4 sm:px-6 pb-4">
                    @include('install.partials.steps', ['current' => $installStepKey])
                </div>
            @endif
        </header>

        <main class="flex-1 py-8 sm:py-12 px-4">
            <div class="max-w-4xl mx-auto">

                @if (session('error') || (isset($errors) && $errors->any()))
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 flex items-start gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10A8 8 0 11 2 10a8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            @if (isset($errors) && $errors->any())
                                <ul class="space-y-0.5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @else
                                {{ session('error') }}
                            @endif
                        </div>
                    </div>
                @endif

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm shadow-slate-200/60 overflow-hidden">
                    @yield('container')
                </div>
            </div>
        </main>

        <footer class="py-6 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} {{ config('app.name', 'AceCademy') }}. {{ _lang('All rights reserved.') }}
        </footer>
    </div>

    @stack('scripts')
</body>

</html>
