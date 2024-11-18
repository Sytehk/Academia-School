@extends('install.layouts.master')

@section('title', _lang('install_complete'))
@section('step_key', 'complete')

@section('container')
    <div class="p-6 sm:p-10 text-center" x-data="completeStep()">
        <div class="mx-auto mb-5 h-14 w-14 rounded-full bg-emerald-50 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0l-3.5-3.5a1 1 0 111.4-1.4l2.8 2.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd" />
            </svg>
        </div>

        <h1 class="text-xl font-bold text-slate-900 mb-2">{{ _lang('Installation Completed Successfully!') }}</h1>
        <p class="text-sm text-slate-500 mb-1">{{ _lang('You can login now with the default credentials.') }}</p>
        <p class="text-sm text-slate-600 mb-8">
            {{ _lang('Email') }}: <strong class="font-semibold text-slate-800">superadmin@gmail.com</strong>
            &middot;
            {{ _lang('Password') }}: <strong class="font-semibold text-slate-800">12345678</strong>
        </p>

        <div class="flex flex-wrap items-center justify-center gap-3 mb-6">
            <a href="{{ url('/login') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 transition-colors text-white text-sm font-semibold px-5 py-2.5 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zM4 9a1 1 0 000 2h7.6l-2.3 2.3a1 1 0 101.4 1.4l4-4a1 1 0 000-1.4l-4-4a1 1 0 10-1.4 1.4L11.6 9H4z" />
                </svg>
                {{ _lang('Login Page') }}
            </a>

            <button @click="checkStatus()" :disabled="checking"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-300 hover:bg-slate-50 disabled:opacity-60 transition-colors text-slate-700 text-sm font-semibold px-5 py-2.5">
                <svg x-show="checking" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span x-text="checking ? '{{ _lang('Checking...') }}' : '{{ _lang('Check API / DB Status') }}'"></span>
            </button>
        </div>

        <div x-show="result" x-cloak
            :class="ok ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700'"
            class="inline-block rounded-xl border px-4 py-2.5 text-sm" x-text="result"></div>
    </div>
@endsection

@push('scripts')
    <script>
        function completeStep() {
            return {
                checking: false,
                result: '',
                ok: false,

                async checkStatus() {
                    this.checking = true;
                    this.result = '';

                    try {
                        const response = await fetch('{{ url('/api/check-status') }}', {
                            headers: { 'Accept': 'application/json' },
                        });
                        const data = await response.json();

                        this.ok = !!data.status;
                        this.result = this.ok
                            ? '{{ _lang('API & Database are working perfectly!') }}'
                            : '{{ _lang('Something went wrong with API or Database.') }}';
                    } catch (e) {
                        this.ok = false;
                        this.result = '{{ _lang('Server error occurred!') }}';
                    } finally {
                        this.checking = false;
                    }
                },
            };
        }
    </script>
@endpush
