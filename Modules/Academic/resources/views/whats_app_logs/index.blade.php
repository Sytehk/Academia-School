@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Dashboard') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>{{ _lang('WhatsApp Delivery Logs') }}</h4>

            <div>
                <a href="{{ route('whats-app-logs.export-pdf', request()->query()) }}" class="btn btn-sm btn-secondary">
                    {{ _lang('Export PDF') }}
                </a>
                <a href="{{ route('whats-app-logs.export-excel', request()->query()) }}" class="btn btn-sm btn-secondary">
                    {{ _lang('Export Excel') }}
                </a>
            </div>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('whats-app-logs.index') }}" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">{{ _lang('Status') }}</label>
                    <select name="status" class="form-control">
                        <option value="">{{ _lang('All') }}</option>
                        <option value="pending" @selected(request('status') == 'pending')>{{ _lang('Pending') }}</option>
                        <option value="queued" @selected(request('status') == 'queued')>{{ _lang('Queued') }}</option>
                        <option value="sent" @selected(request('status') == 'sent')>{{ _lang('Sent') }}</option>
                        <option value="failed" @selected(request('status') == 'failed')>{{ _lang('Failed') }}</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">{{ _lang('From Date') }}</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">{{ _lang('To Date') }}</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">{{ _lang('Filter') }}</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr>
                            <th>{{ _lang('#') }}</th>
                            <th>{{ _lang('Student') }}</th>
                            <th>{{ _lang('Phone') }}</th>
                            <th>{{ _lang('Message') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Retries') }}</th>
                            <th>{{ _lang('Sent At') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>{{ $log->student?->user?->name ?? '-' }}</td>
                                <td>{{ $log->phone }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($log->message, 60) }}</td>
                                <td>
                                    @php
                                        $badge = match ($log->status) {
                                            'sent' => 'success',
                                            'failed' => 'danger',
                                            'queued' => 'info',
                                            default => 'warning',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badge }}">{{ ucfirst($log->status) }}</span>
                                </td>
                                <td>{{ $log->retry_count }}</td>
                                <td>{{ $log->created_at?->format('d-m-Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">{{ _lang('No WhatsApp logs found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $logs->links() }}
        </div>
    </div>
@endsection
