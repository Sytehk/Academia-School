@extends('install.layouts.master')

@section('title', _lang('install_installation'))
@section('step_key', 'installation')

@section('container')
    <div class="p-6 sm:p-10" x-data="{ installing: false }">
        <h1 class="text-xl font-bold text-slate-900 mb-1">{{ _lang('install_welcome') }}</h1>
        <p class="text-sm text-slate-500 mb-6">{{ _lang('install_msg') }}</p>

        <div class="rounded-xl border border-slate-200 bg-slate-50 p-5 mb-6">
            <h2 class="text-sm font-semibold text-slate-800 mb-3">{{ _lang('What\'s Included') }}</h2>
            <ul class="grid sm:grid-cols-2 gap-2">
                @foreach ([
                    'School Management System',
                    'Staff Management System',
                    'Accounts & Financial Management',
                    'Authentication & Role-Based Permissions',
                    'Global Settings & Configurations',
                    'Notifications & SMS Module',
                    'HRM Management',
                    'Reports',
                ] as $feature)
                    <li class="flex items-center gap-2 text-sm text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-blue-500 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0l-3.5-3.5a1 1 0 111.4-1.4l2.8 2.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd" />
                        </svg>
                        {{ _lang($feature) }}
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="rounded-xl border border-blue-100 bg-blue-50/60 p-5 mb-8">
            <h2 class="text-sm font-semibold text-slate-800 mb-3">{{ _lang('This step will') }}</h2>
            <ul class="space-y-1.5">
                @foreach ([
                    'Verify database connectivity and privileges',
                    'Generate the application encryption key',
                    'Import the initial database schema and seed data',
                    'Link public storage for uploaded files',
                    'Clear and rebuild application caches',
                ] as $task)
                    <li class="flex items-center gap-2 text-sm text-slate-600">
                        <span class="h-1.5 w-1.5 rounded-full bg-blue-500 flex-shrink-0"></span>
                        {{ _lang($task) }}
                    </li>
                @endforeach
            </ul>
        </div>

        <div x-show="installing" x-cloak class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 flex items-center gap-2">
            <svg class="h-4 w-4 animate-spin flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            {{ _lang('Installing... please do not close or refresh this page.') }}
        </div>

        <form action="{{ url('install/installation') }}" method="post" @submit="installing = true">
            @csrf
            <div class="flex justify-end">
                <button type="submit" :disabled="installing"
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-60 transition-colors text-white text-sm font-semibold px-6 py-2.5 shadow-sm">
                    <span x-text="installing ? '{{ _lang('Installing...') }}' : '{{ _lang('install_install') }}'"></span>
                </button>
            </div>
        </form>
    </div>
@endsection
