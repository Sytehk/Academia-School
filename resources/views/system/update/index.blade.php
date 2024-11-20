@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('System Update') }}
@endsection

@section('content')
    <div class="card mb-5">
        <div class="card-header">
            <h3 class="card-title">{{ _lang('System Update') }}</h3>
        </div>
        <div class="card-body">
            <div class="row g-4 mb-6">
                <div class="col-md-4">
                    <div class="text-muted">{{ _lang('Current Version') }}</div>
                    <div class="fs-2 fw-bold">{{ $currentVersion }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted">{{ _lang('Latest Version') }}</div>
                    <div class="fs-2 fw-bold">{{ $latestVersion ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted">{{ _lang('Status') }}</div>
                    <div class="fs-4 fw-bold {{ $updateAvailable ? 'text-warning' : 'text-success' }}">
                        @if ($demoRestricted)
                            {{ _lang('Disabled in Demo Mode') }}
                        @else
                            {{ $updateAvailable ? _lang('Update Available') : _lang('System is up to date.') }}
                        @endif
                    </div>
                </div>
            </div>

            @if ($isLocked)
                <div class="alert alert-warning">{{ _lang('Another system update is currently in progress.') }}</div>
            @endif

            <form method="POST" action="{{ route('system.update.check') }}" class="mb-6">
                @csrf
                <button type="submit" class="btn btn-light-primary" @disabled($demoRestricted)>
                    {{ _lang('Check for Updates') }}
                </button>
            </form>

            <hr>

            <h4 class="mb-4">{{ _lang('Manual Update') }}</h4>

            @if ($allowManualUpload && ! $demoRestricted)
                <form method="POST" action="{{ route('system.update.validate') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label">{{ _lang('Upload Update Package') }}</label>
                        <input type="file" name="update_package" class="form-control" accept=".zip" required>
                        <div class="form-text">{{ _lang('Upload a ZIP package containing update.json and files/.') }}</div>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ _lang('Validate Package') }}</button>
                </form>
            @else
                <div class="alert alert-info">
                    {{ _lang('Manual updates are disabled in demo mode or by configuration.') }}
                </div>
            @endif
        </div>
    </div>

    @if (session('update_logs'))
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ _lang('Update Log') }}</h3></div>
            <div class="card-body">
                <pre class="mb-0">@foreach (session('update_logs') as $log)[{{ $log['time'] }}] {{ $log['message'] }}
@endforeach</pre>
            </div>
        </div>
    @endif
@endsection
