<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    {{-- Responsive --}}
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- CSRF --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @yield('title', config('app.name')) | {{ config('app.name') }}
    </title>

    {{-- SEO --}}
    <meta name="description" content="{{ config('app.name') }}">
    <meta name="author" content="{{ config('app.name') }}">

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ get_logo() }}">

    @include('partials.local-assets.head', ['fontawesome' => 'v4'])

    {{-- Global Styles --}}
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            background: #020617;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #0f172a;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, #2563eb, #4f46e5);
            border-radius: 50px;
        }

        ::selection {
            background: #2563eb;
            color: white;
        }

        /* Autofill Fix */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        textarea:-webkit-autofill,
        select:-webkit-autofill {
            -webkit-text-fill-color: #fff !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        /* Remove Number Input Arrow */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Glass Effect */
        .glass {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        /* Gradient Text */
        .gradient-text {
            background: linear-gradient(to right, #60a5fa, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>

    {{-- Page Styles --}}
    @yield('styles')
</head>

<body class="font-sans antialiased text-white">

    {{-- App --}}
    <div id="app">

        {{-- Main Content --}}
        <main class="min-h-screen">
            @yield('content')
        </main>

    </div>

    {{-- JQuery + Alpine (bundled in Vite app.js) --}}
    @include('partials.local-assets.scripts', ['jquery' => true, 'alpine' => true])

    {{-- Custom Scripts --}}
    @yield('scripts')

</body>

</html>
