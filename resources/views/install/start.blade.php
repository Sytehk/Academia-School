@extends('install.layouts.master')

@section('title', _lang('start_install'))
@section('step_key', 'start')

@section('container')
    <div class="p-6 sm:p-10">
        <h1 class="text-2xl font-bold text-slate-900 mb-1">{{ _lang('Welcome to the Installation Wizard') }}</h1>
        <p class="text-sm text-slate-500 mb-6">{{ config('app.name', 'AceCademy') }} {{ _lang('Software Installation') }}</p>

        <p class="text-sm text-slate-600 leading-relaxed mb-4">
            {{ _lang('Please proceed step by step with proper data according to the instructions.') }}
        </p>
        <p class="text-sm text-slate-600 leading-relaxed mb-6">
            {{ _lang('Before starting the installation process, please collect the following information. Without this information, you won\'t be able to complete the installation.') }}
        </p>

        <div class="rounded-xl border border-blue-100 bg-blue-50/60 p-5 mb-8">
            <h2 class="text-sm font-semibold text-slate-800 mb-3 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10A8 8 0 11 2 10a8 8 0 0116 0zm-8-4a1 1 0 00-1 1v4a1 1 0 102 0V7a1 1 0 00-1-1zm0 8a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
                </svg>
                {{ _lang('Required Information') }}
            </h2>
            <ul class="space-y-2">
                @foreach ([
                    'Host Name' => 'e.g., 127.0.0.1 or your server IP',
                    'Database Name' => 'The name of the MySQL database',
                    'Database Username' => 'MySQL user with privileges',
                    'Database Password' => 'Password for the above user',
                ] as $label => $desc)
                    <li class="flex items-start gap-2 text-sm text-slate-600">
                        <span class="mt-1.5 h-1.5 w-1.5 rounded-full bg-blue-500 flex-shrink-0"></span>
                        <span><strong class="text-slate-800 font-medium">{{ _lang($label) }}</strong> &ndash; {{ _lang($desc) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="flex justify-end">
            <a href="{{ url('install/requirements') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 transition-colors text-white text-sm font-semibold px-5 py-2.5 shadow-sm">
                {{ _lang('install_next') }}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10.3 3.3a1 1 0 011.4 0l5 5a1 1 0 010 1.4l-5 5a1 1 0 01-1.4-1.4L13.6 10l-3.3-3.3a1 1 0 010-1.4z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>
    </div>
@endsection
