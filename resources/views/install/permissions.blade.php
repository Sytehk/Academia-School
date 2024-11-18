@extends('install.layouts.master')

@section('title', _lang('install_permissions'))
@section('step_key', 'permissions')

@section('container')
    <div class="p-6 sm:p-10">
        <h1 class="text-xl font-bold text-slate-900 mb-1">{{ _lang('Folder Permissions') }}</h1>
        <p class="text-sm text-slate-500 mb-6">{{ _lang('These folders must be writable by the web server.') }}</p>

        <ul class="divide-y divide-slate-100 rounded-xl border border-slate-200 overflow-hidden mb-8">
            @foreach ($permissions as $path => $isWritable)
                <li class="flex items-center justify-between px-4 py-3 text-sm {{ $isWritable ? 'bg-white' : 'bg-red-50/50' }}">
                    <span class="font-mono text-slate-700">{{ $path }}</span>
                    @if ($isWritable)
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold px-2.5 py-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0l-3.5-3.5a1 1 0 111.4-1.4l2.8 2.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd" />
                            </svg>
                            {{ _lang('Writable') }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-red-50 text-red-700 text-xs font-semibold px-2.5 py-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.7 7.3a1 1 0 00-1.4 1.4L8.6 10l-1.3 1.3a1 1 0 101.4 1.4L10 11.4l1.3 1.3a1 1 0 001.4-1.4L11.4 10l1.3-1.3a1 1 0 00-1.4-1.4L10 8.6 8.7 7.3z" clip-rule="evenodd" />
                            </svg>
                            {{ _lang('Not Writable') }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ul>

        <div class="flex items-center justify-end gap-3">
            @if ($next)
                <a href="{{ route('install.database') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 transition-colors text-white text-sm font-semibold px-5 py-2.5 shadow-sm">
                    {{ _lang('install_next') }}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.3 3.3a1 1 0 011.4 0l5 5a1 1 0 010 1.4l-5 5a1 1 0 01-1.4-1.4L13.6 10l-3.3-3.3a1 1 0 010-1.4z" clip-rule="evenodd" />
                    </svg>
                </a>
            @else
                <p class="text-sm text-red-600 mr-auto">{{ _lang('Some folders are not writable. Please update permissions.') }}</p>
                <a href="{{ Request::url() }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-300 hover:bg-slate-50 transition-colors text-slate-700 text-sm font-semibold px-5 py-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 10a6 6 0 0110.5-3.95l.9-.9a.75.75 0 011.28.53v3.07a.75.75 0 01-.75.75h-3.07a.75.75 0 01-.53-1.28l.86-.86A4.5 4.5 0 105.5 10a.75.75 0 01-1.5 0z" clip-rule="evenodd" />
                    </svg>
                    {{ _lang('refresh') }}
                </a>
            @endif
        </div>
    </div>
@endsection
