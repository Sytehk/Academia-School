@extends('install.layouts.master')

@section('title', _lang('install_database'))
@section('step_key', 'database')

@section('container')
    <div class="p-6 sm:p-10" x-data="databaseStep()">

        <h1 class="text-xl font-bold text-slate-900 mb-1">{{ _lang('Database Configuration') }}</h1>
        <p class="text-sm text-slate-500 mb-6">{{ _lang('Provide your MySQL database connection details.') }}</p>

        <div x-show="error" x-cloak x-text="error"
            class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

        <div class="grid sm:grid-cols-2 gap-5 mb-8">
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">{{ _lang('Software Name') }}</label>
                <input type="text" x-model="form.software_name" @input="dirty()"
                    class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">{{ _lang('install_host') }}</label>
                <input type="text" x-model="form.host" @input="dirty()"
                    class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">{{ _lang('install_port') }}</label>
                <input type="number" x-model="form.port" @input="dirty()"
                    class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">{{ _lang('install_name') }}</label>
                <input type="text" x-model="form.name" @input="dirty()"
                    class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">{{ _lang('install_username') }}</label>
                <input type="text" x-model="form.username" @input="dirty()"
                    class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">{{ _lang('install_password') }}</label>
                <div class="relative">
                    <input :type="showPassword ? 'text' : 'password'" x-model="form.password" @input="dirty()"
                        class="w-full rounded-xl border border-slate-300 pl-3.5 pr-10 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition">
                    <button type="button" @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 3.5c-4.4 0-8.1 2.8-9.5 6.5 1.4 3.7 5.1 6.5 9.5 6.5s8.1-2.8 9.5-6.5C18.1 6.3 14.4 3.5 10 3.5zm0 10.8a4.3 4.3 0 110-8.6 4.3 4.3 0 010 8.6z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div x-show="warning" x-cloak x-text="warning"
            class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700"></div>

        <div class="flex items-center justify-end gap-3">
            <button type="button" @click="checkConnection()" :disabled="checking"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-300 hover:bg-slate-50 disabled:opacity-60 transition-colors text-slate-700 text-sm font-semibold px-5 py-2.5">
                <svg x-show="checking" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span x-text="checking ? '{{ _lang('Checking...') }}' : '{{ _lang('Check Connection') }}'"></span>
            </button>

            <button type="button" @click="save()" :disabled="!connectionOk || saving"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-white text-sm font-semibold px-5 py-2.5 shadow-sm">
                <svg x-show="saving" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span x-text="saving ? '{{ _lang('Saving...') }}' : '{{ _lang('install_next') }}'"></span>
            </button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function databaseStep() {
            return {
                form: {
                    software_name: '{{ old('software_name') }}',
                    host: '127.0.0.1',
                    port: 3306,
                    name: '{{ old('name') }}',
                    username: '{{ old('username') ?? 'root' }}',
                    password: '',
                },
                showPassword: false,
                checking: false,
                saving: false,
                connectionOk: false,
                error: '',
                warning: '',

                dirty() {
                    this.connectionOk = false;
                    this.error = '';
                    this.warning = '';
                },

                csrf() {
                    return document.querySelector('meta[name="csrf-token"]').content;
                },

                async checkConnection() {
                    this.error = '';
                    this.warning = '';
                    this.checking = true;

                    try {
                        const response = await fetch('{{ route('install.check_database') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrf(),
                            },
                            body: JSON.stringify(this.form),
                        });

                        const data = await response.json();

                        if (data.status === 'success') {
                            this.connectionOk = true;
                            this.warning = data.warning || '';
                        } else {
                            this.connectionOk = false;
                            this.error = data.message || '{{ _lang('Database connection failed.') }}';
                        }
                    } catch (e) {
                        this.error = '{{ _lang('Server error occurred.') }}';
                    } finally {
                        this.checking = false;
                    }
                },

                async save() {
                    if (!this.connectionOk) {
                        return;
                    }

                    this.error = '';
                    this.saving = true;

                    try {
                        const response = await fetch('{{ route('install.database.save') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrf(),
                            },
                            body: JSON.stringify(this.form),
                        });

                        const data = await response.json();

                        if (data.status === 'success') {
                            window.location.href = data.redirect;
                        } else {
                            this.error = data.message || '{{ _lang('Failed to save database credentials.') }}';
                        }
                    } catch (e) {
                        this.error = '{{ _lang('Server error occurred.') }}';
                    } finally {
                        this.saving = false;
                    }
                },
            };
        }
    </script>
@endpush
