@extends('layouts.app')

@section('title')
    {{ _lang('Login') }}
@endsection

@section('content')
    <div class="relative min-h-screen overflow-hidden bg-gradient-to-br from-slate-950 via-blue-950 to-indigo-950">

        {{-- Background Effects --}}
        <div class="absolute top-0 left-0 rounded-full w-96 h-96 bg-blue-500/20 blur-3xl"></div>
        <div class="absolute bottom-0 right-0 rounded-full w-96 h-96 bg-indigo-500/20 blur-3xl"></div>

        {{-- Header --}}
        <header class="relative z-20 border-b border-white/10 backdrop-blur-xl bg-white/5">
            <div class="flex items-center justify-between px-6 py-4 mx-auto max-w-7xl">

                {{-- Logo --}}
                @if (!empty($instituteImage?->header_logo_light_theme))
                    <a href="{{ url('/') }}" class="flex items-center gap-3">
                        <img src="{{ asset('storage/institute_image_settings/' . $instituteImage->header_logo_light_theme) }}"
                            alt="" class="w-auto shadow-2xl h-14 rounded-xl">
                    </a>
                @else
                    <a href="{{ url('/') }}" class="flex items-center gap-3">
                        <img src="{{ asset('assets/media/logos/default.png') }}" alt=""
                            class="w-auto shadow-2xl h-14 rounded-xl">
                    </a>
                @endif

                {{-- Tutorial --}}
                {{-- <a href="#" class="flex items-center gap-2 text-white transition duration-300 hover:text-blue-300">
                    <i class="text-2xl text-red-500 fa fa-youtube-play"></i>
                    <span class="hidden font-semibold md:block">
                        {{ _lang('Tutorial Center') }}
                    </span>
                </a> --}}
            </div>
        </header>

        {{-- Main Section --}}
        <section class="relative z-10 px-4 py-10">
            <div
                class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 overflow-hidden rounded-[40px] border border-white/10 bg-white/5 backdrop-blur-2xl shadow-[0_20px_80px_rgba(0,0,0,0.45)]">

                {{-- Left Side --}}
                <div
                    class="relative flex flex-col justify-center p-8 border-b lg:p-14 lg:border-b-0 lg:border-r border-white/10">

                    {{-- Decorative --}}
                    <div class="absolute top-0 right-0 w-40 h-40 rounded-full bg-blue-500/10 blur-3xl">
                    </div>

                    <div class="relative z-10 text-center">

                        <div
                            class="inline-flex items-center justify-center mb-8 rounded-full shadow-2xl w-28 h-28 bg-gradient-to-r from-blue-500 to-indigo-600 animate-pulse">

                            <img src="{{ asset('images/login-lock.png') }}" class="object-contain w-16 h-16" alt="">
                        </div>

                        <img src="{{ asset('images/login-bg.png') }}" class="w-full max-w-md mx-auto mb-8 drop-shadow-2xl"
                            alt="login image">

                        <h2 class="mb-6 text-3xl font-black leading-tight text-white lg:text-4xl">
                            {{ _lang('Welcome Back') }}
                        </h2>

                        <p class="mb-8 text-lg leading-relaxed text-slate-300">
                            {{ _lang('Securely access your institute dashboard and manage your academic activities easily.') }}
                        </p>

                        {{-- Instructions --}}
                        <div class="p-6 text-left border bg-white/5 border-white/10 rounded-3xl backdrop-blur-xl">

                            <h3 class="flex items-center gap-2 mb-5 text-xl font-bold text-white">
                                <i class="text-blue-400 fa fa-info-circle"></i>
                                {{ _lang('Login Instructions') }}
                            </h3>

                            <ul class="space-y-4 text-slate-300">
                                <li class="flex items-start gap-3">
                                    <span
                                        class="flex items-center justify-center text-sm font-bold text-white bg-blue-500 rounded-full w-7 h-7">
                                        ✓
                                    </span>
                                    <span>
                                        {{ _lang('Login with your Email / Phone and Password.') }}
                                    </span>
                                </li>

                                <li class="flex items-start gap-3">
                                    <span
                                        class="flex items-center justify-center text-sm font-bold text-white bg-indigo-500 rounded-full w-7 h-7">
                                        ✓
                                    </span>
                                    <span>
                                        {{ _lang('Your personal dashboard will appear after login.') }}
                                    </span>
                                </li>
                            </ul>
                        </div>

                    </div>
                </div>

                {{-- Right Side --}}
                <div class="flex items-center p-6 lg:p-14">

                    <div class="w-full max-w-lg mx-auto">

                        {{-- Title --}}
                        <div class="mb-10 text-center">

                            <div
                                class="inline-flex items-center justify-center w-24 h-24 mb-6 border rounded-full shadow-2xl bg-white/10 border-white/20">

                                <img src="{{ asset('images/password-key.png') }}" class="w-12 h-12" alt="">
                            </div>

                            <h1 class="mb-4 text-4xl font-black text-white lg:text-5xl">
                                {{ _lang('Sign In') }}
                            </h1>

                            <p class="text-lg text-slate-300">
                                {{ _lang('Enter your credentials to continue') }}
                            </p>
                        </div>

                        {{-- Success Message --}}
                        @if (session('success'))
                            <div
                                class="px-5 py-4 mb-6 border rounded-2xl border-emerald-400/30 bg-emerald-500/10 text-emerald-300 backdrop-blur-xl">
                                {{ session('success') }}
                            </div>
                        @endif

                        {{-- Error Message --}}
                        @if (session('error'))
                            <div
                                class="px-5 py-4 mb-6 text-red-300 border rounded-2xl border-red-400/30 bg-red-500/10 backdrop-blur-xl">
                                {{ session('error') }}
                            </div>
                        @endif

                        {{-- Form --}}
                        <form method="POST" action="{{ route('login') }}" class="space-y-6">

                            @csrf

                            {{-- Email --}}
                            <div>
                                <label class="block mb-3 text-sm font-semibold text-slate-200">
                                    {{ _lang('Email / Phone') }}
                                </label>

                                <div class="relative">

                                    <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-slate-400">
                                        <i class="text-lg fa fa-user"></i>
                                    </span>

                                    <input type="text" name="email" value="{{ old('email') }}" required autofocus
                                        placeholder="{{ _lang('Enter Email or Phone') }}"
                                        class="w-full h-16 pr-5 text-white transition duration-300 border outline-none rounded-2xl border-white/10 bg-white/10 pl-14 placeholder-slate-400 focus:border-blue-400 focus:ring-4 focus:ring-blue-500/20">

                                </div>

                                @error('email')
                                    <p class="mt-2 text-sm text-red-400">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Password --}}
                            <div>
                                <label class="block mb-3 text-sm font-semibold text-slate-200">
                                    {{ _lang('Password') }}
                                </label>

                                <div class="relative">

                                    <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-slate-400">
                                        <i class="text-lg fa fa-lock"></i>
                                    </span>

                                    <input type="password" name="password" required
                                        placeholder="{{ _lang('Enter Password') }}"
                                        class="w-full h-16 pr-5 text-white transition duration-300 border outline-none rounded-2xl border-white/10 bg-white/10 pl-14 placeholder-slate-400 focus:border-blue-400 focus:ring-4 focus:ring-blue-500/20">

                                </div>

                                @error('password')
                                    <p class="mt-2 text-sm text-red-400">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Remember + Forgot --}}
                            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

                                <label class="flex items-center gap-3 cursor-pointer">

                                    <input type="checkbox" name="remember"
                                        class="w-5 h-5 text-blue-500 rounded border-white/20 bg-white/10 focus:ring-blue-500">

                                    <span class="text-slate-300">
                                        {{ _lang('Remember Me') }}
                                    </span>
                                </label>

                                {{-- <a href="{{ route('password.request') }}"
                                    class="font-medium text-blue-300 transition hover:text-blue-200">
                                    {{ _lang('Forgot Password?') }}
                                </a> --}}
                            </div>

                            {{-- Login Button --}}
                            <button type="submit" id="login-btn"
                                class="w-full h-16 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-lg font-bold shadow-2xl transition duration-300 hover:scale-[1.02]">
                                <i class="mr-2 fa fa-sign-in"></i>
                                {{ _lang('Login Now') }}
                            </button>

                            {{-- Demo Buttons --}}
                            @if (is_demo_mode())
                                <div class="grid grid-cols-1 gap-4 pt-4 md:grid-cols-2">

                                    <button type="button" id="super-admin-login-btn"
                                        class="flex items-center justify-start px-4 font-semibold text-white transition duration-300 border h-14 rounded-2xl border-white/10 bg-white/10 hover:bg-indigo-500/20">
                                        <i class="w-5 mr-3 text-indigo-400 fa fa-user-shield"></i>
                                        {{ _lang('Admin') }}
                                    </button>

                                    <button type="button" id="accountant-login-btn"
                                        class="flex items-center justify-start px-4 font-semibold text-white transition duration-300 border h-14 rounded-2xl border-white/10 bg-white/10 hover:bg-green-500/20">
                                        <i class="w-5 mr-3 text-green-400 fa fa-calculator"></i>
                                        {{ _lang('Accountant') }}
                                    </button>

                                    <button type="button" id="librarian-login-btn"
                                        class="flex items-center justify-start px-4 font-semibold text-white transition duration-300 border h-14 rounded-2xl border-white/10 bg-white/10 hover:bg-yellow-500/20">
                                        <i class="w-5 mr-3 text-yellow-400 fa fa-book"></i>
                                        {{ _lang('Librarian') }}
                                    </button>

                                    <button type="button" id="teacher-login-btn"
                                        class="flex items-center justify-start px-4 font-semibold text-white transition duration-300 border h-14 rounded-2xl border-white/10 bg-white/10 hover:bg-purple-500/20">
                                        <i class="w-5 mr-3 fa fa-black-tie"></i>
                                        {{ _lang('Teacher') }}
                                    </button>

                                    <button type="button" id="student-login-btn"
                                        class="flex items-center justify-start px-4 font-semibold text-white transition duration-300 border h-14 rounded-2xl border-white/10 bg-white/10 hover:bg-orange-500/20">
                                        <i class="w-5 mr-3 fa fa-graduation-cap"></i>
                                        {{ _lang('Student') }}
                                    </button>
                                </div>
                            @endif

                        </form>

                    </div>
                </div>
            </div>
        </section>

        {{-- Footer --}}
        <footer class="relative z-10 mt-10 border-t border-white/10 bg-black/30 backdrop-blur-xl">
            <div class="px-6 py-6 mx-auto text-center max-w-7xl">
                <p class="text-sm text-slate-400 md:text-base">
                    @if (get_option('copyright_text'))
                        {!! get_option('copyright_text') !!}
                    @else
                        © {{ date('Y') }}
                        <span class="font-semibold text-blue-400">
                            {{ config('app.name') }}
                        </span>
                        — {{ _lang('All Rights Reserved.') }}
                    @endif
                </p>
            </div>
        </footer>
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const demoUsers = {
            'super-admin-login-btn': {
                email: 'superadmin@gmail.com',
                password: '12345678'
            },
            'accountant-login-btn': {
                email: 'accountant@gmail.com',
                password: '12345678'
            },
            'librarian-login-btn': {
                email: 'librarian@gmail.com',
                password: '12345678'
            },
            'teacher-login-btn': {
                email: 'teacher1@gmail.com',
                password: '12345678'
            },
            'student-login-btn': {
                email: 'student@gmail.com',
                password: '12345678'
            }
        };

        Object.keys(demoUsers).forEach(function(id) {

            const btn = document.getElementById(id);

            if (btn) {
                btn.addEventListener('click', function() {

                    document.querySelector('input[name="email"]').value =
                        demoUsers[id].email;

                    document.querySelector('input[name="password"]').value =
                        demoUsers[id].password;

                    document.getElementById('login-btn').click();
                });
            }
        });
    });
</script>
