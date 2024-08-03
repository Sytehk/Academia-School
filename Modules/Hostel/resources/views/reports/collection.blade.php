@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Hostel Collection Report') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">{{ _lang('From') }}</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ _lang('To') }}</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary w-100">{{ _lang('Filter') }}</button>
                </div>
            </form>

            <div class="d-flex justify-content-between mb-3">
                <h5>{{ _lang('Total Collected') }}: {{ format_currency($totalCollected) }}</h5>
                <div>
                    @can('hostel_report_export')
                        <a href="{{ route('hostel-reports.export-pdf', request()->query()) }}" class="btn btn-sm btn-secondary">{{ _lang('Export PDF') }}</a>
                        <a href="{{ route('hostel-reports.export-excel', request()->query()) }}" class="btn btn-sm btn-secondary">{{ _lang('Export Excel') }}</a>
                    @endcan
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>{{ _lang('Invoice') }}</th>
                            <th>{{ _lang('Student') }}</th>
                            <th>{{ _lang('Room') }}</th>
                            <th>{{ _lang('Paid') }}</th>
                            <th>{{ _lang('Due') }}</th>
                            <th>{{ _lang('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($collections as $collection)
                            <tr>
                                <td>{{ $collection->invoice_id }}</td>
                                <td>{{ $collection->student?->user?->name ?? '-' }}</td>
                                <td>{{ $collection->roomMember?->room?->room_number ?? '-' }}</td>
                                <td>{{ $collection->total_paid }}</td>
                                <td>{{ $collection->total_due }}</td>
                                <td>{{ $collection->invoice_date }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">{{ _lang('No collections found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
