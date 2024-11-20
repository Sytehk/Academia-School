@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('System Information') }}
@endsection

@section('content')
    <div class="card mb-5">
        <div class="card-header">
            <h3 class="card-title">{{ _lang('System Information') }}</h3>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="border rounded p-4 h-100">
                        <div class="text-muted">{{ _lang('Current Version') }}</div>
                        <div class="fs-2 fw-bold">{{ $currentVersion }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-4 h-100">
                        <div class="text-muted">{{ _lang('Latest Version') }}</div>
                        <div class="fs-2 fw-bold">{{ $latestVersion ?? '—' }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-4 h-100">
                        <div class="text-muted">{{ _lang('Status') }}</div>
                        <div class="fs-4 fw-bold {{ $updateAvailable ? 'text-warning' : 'text-success' }}">
                            {{ $updateAvailable ? _lang('Update Available') : _lang('System is up to date.') }}
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            <div class="row g-3">
                <div class="col-md-3"><strong>{{ _lang('PHP') }}:</strong> {{ $phpVersion }}</div>
                <div class="col-md-3"><strong>{{ _lang('Laravel') }}:</strong> {{ $laravelVersion }}</div>
                <div class="col-md-3"><strong>{{ _lang('Environment') }}:</strong> {{ $environment }}</div>
                <div class="col-md-3">
                    <strong>{{ _lang('Demo Mode') }}:</strong>
                    <span class="badge {{ $demoMode ? 'badge-warning' : 'badge-success' }}">
                        {{ $demoMode ? _lang('Enabled') : _lang('Disabled') }}
                    </span>
                </div>
                <div class="col-md-3">
                    <strong>{{ _lang('Maintenance') }}:</strong>
                    {{ $maintenanceMode ? _lang('On') : _lang('Off') }}
                </div>
            </div>
        </div>
    </div>
@endsection
