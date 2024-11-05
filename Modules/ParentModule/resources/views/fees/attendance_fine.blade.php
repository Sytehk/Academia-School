@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Attendance Fine') }}
@endsection

@section('content')
    @include('parentmodule::partials.child_context')

    <div class="card">
        <div class="card-header align-items-center">
            <h3 class="card-title">{{ _lang('Attendance Fine') }} ({{ current_year() }})</h3>
            <div class="card-toolbar">
                <span class="fs-5 fw-bold text-danger">
                    {{ _lang('Total Fine') }}: {{ $report['total_fine'] }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="text-muted mb-4">{{ _lang('Fine per absent day') }}: <b>{{ $report['fine_per_day'] ?? '0.00' }}</b></div>
            <div class="table-responsive">
                <table class="table table-row-bordered table-row-dashed gy-2 align-middle">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="ps-4">{{ _lang('Month') }}</th>
                            <th class="text-center">{{ _lang('Absent Days') }}</th>
                            <th class="text-end pe-4">{{ _lang('Fine Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($report['months'] as $month)
                            <tr>
                                <td class="ps-4 fw-semibold">{{ $month['month_name'] }}</td>
                                <td class="text-center">{{ $month['absent_count'] }}</td>
                                <td class="text-end pe-4">{{ $month['fine_amount'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-8">{{ _lang('No attendance fine recorded.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
