@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Update Details') }}
@endsection

@section('content')
    <div class="card mb-5">
        <div class="card-header">
            <h3 class="card-title">{{ $update->from_version }} → {{ $update->to_version }}</h3>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-3"><strong>{{ _lang('Status') }}:</strong> {{ $update->status }}</div>
                <div class="col-md-3"><strong>{{ _lang('Type') }}:</strong> {{ $update->type }}</div>
                <div class="col-md-3"><strong>{{ _lang('Package') }}:</strong> {{ $update->package_name }}</div>
                <div class="col-md-3"><strong>{{ _lang('Duration') }}:</strong> {{ $update->duration() ? $update->duration().'s' : '—' }}</div>
            </div>

            @if ($update->error_message)
                <div class="alert alert-danger">{{ $update->error_message }}</div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">{{ _lang('Logs') }}</h3></div>
        <div class="card-body">
            <pre class="mb-0">@foreach ($update->logs as $log)[{{ $log->logged_at?->format('H:i:s') }}] {{ $log->message }}
@endforeach</pre>
        </div>
    </div>
@endsection
