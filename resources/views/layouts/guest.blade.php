<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>

    <link rel="icon" type="image/png" href="{{ get_logo() }}">

    @include('partials.local-assets.head')
</head>

<body class="font-[Inter] antialiased bg-gray-100">
    <div class="flex flex-col items-center justify-center min-h-screen px-4 py-6 sm:pt-0">
        <a href="{{ url('/') }}">
            <img src="{{ get_logo() }}" alt="{{ config('app.name') }}" class="w-auto h-16">
        </a>

        <div class="w-full px-6 py-4 mt-6 overflow-hidden bg-white shadow-md sm:max-w-md sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>
</body>

</html>
