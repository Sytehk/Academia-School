@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('System Settings') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ _lang('System Settings') }}</h3>
        </div>
        <div class="card-body">
            @if ($demoMode)
                <div class="alert alert-warning">
                    {{ _lang('System settings cannot be changed while demo mode is enabled.') }}
                </div>
            @endif

            <form method="POST" action="{{ route('system.settings.update') }}">
                @csrf

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Update Server URL') }}</label>
                        <input type="url" name="update_server_url" class="form-control"
                            value="{{ $settings['update_server_url'] ?? config('system.updates.remote_check_url') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Demo Reset Interval (hours)') }}</label>
                        <input type="number" name="demo_reset_interval" class="form-control" min="1" max="168"
                            value="{{ $settings['demo_reset_interval'] ?? config('system.demo.reset_interval') }}">
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="update_check_enabled" value="1"
                                @checked($settings['update_check_enabled'] ?? config('system.updates.remote_check_enabled'))>
                            <label class="form-check-label">{{ _lang('Enable remote update check') }}</label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="demo_auto_reset" value="1"
                                @checked($settings['demo_auto_reset'] ?? config('system.demo.auto_reset'))>
                            <label class="form-check-label">{{ _lang('Enable demo auto reset') }}</label>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="btn btn-primary"
                        @disabled($demoMode)>{{ _lang('Save Settings') }}</button>
                </div>
            </form>
        </div>
    </div>

    @if ($migrationsPending)
        <div class="mt-4 card">
            <div class="card-header">
                <h3 class="card-title">{{ _lang('Database Update') }}</h3>
            </div>
            <div class="card-body">
                <p class="mb-4">
                    {{ _lang('Some database migrations have not been run yet. Update the database to apply them.') }}</p>
                <form method="POST" action="{{ route('system.settings.migrate') }}"
                    onsubmit="return confirm('{{ _lang('Run pending database migrations now?') }}')">
                    @csrf
                    <button type="submit" class="btn btn-warning" @disabled($demoMode)>
                        <i class="fa fa-database"></i> {{ _lang('Update Database') }}
                    </button>
                </form>
            </div>
        </div>
    @endif
@endsection
