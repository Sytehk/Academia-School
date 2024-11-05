@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Attendance') }}
@endsection

@section('content')
    @include('parentmodule::partials.child_context')

    <div class="card">
        <div class="card-header align-items-center">
            <h3 class="card-title">
                {{ _lang('Attendance') }} —
                {{ \Carbon\Carbon::create()->month($attendance['month'])->format('F') }} {{ $attendance['year'] }}
            </h3>
            <div class="card-toolbar">
                <form method="GET" action="{{ route('parent.attendance') }}" class="d-flex gap-2">
                    <select name="month" class="form-select form-select-sm">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $attendance['month'] == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                    <select name="year" class="form-select form-select-sm">
                        @for ($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}" {{ $attendance['year'] == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary">{{ _lang('Filter') }}</button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-5 g-3">
                <div class="col-4">
                    <div class="border border-success border-dashed rounded p-3 text-center">
                        <div class="fs-3 fw-bold text-success">{{ $attendance['summary']['present'] }}</div>
                        <div class="fs-8 text-muted">{{ _lang('Present') }}</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border border-danger border-dashed rounded p-3 text-center">
                        <div class="fs-3 fw-bold text-danger">{{ $attendance['summary']['absent'] }}</div>
                        <div class="fs-8 text-muted">{{ _lang('Absent') }}</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border border-warning border-dashed rounded p-3 text-center">
                        <div class="fs-3 fw-bold text-warning">{{ $attendance['summary']['late'] }}</div>
                        <div class="fs-8 text-muted">{{ _lang('Late') }}</div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-row-bordered table-row-dashed gy-2 align-middle">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="ps-4">{{ _lang('Date') }}</th>
                            <th>{{ _lang('Day') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Check In') }}</th>
                            <th class="text-end pe-4">{{ _lang('Check Out') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($attendance['days'] as $day)
                            <tr>
                                <td class="ps-4">{{ \Carbon\Carbon::parse($day['date'])->format('d M, Y') }}</td>
                                <td>{{ $day['day'] }}</td>
                                <td>
                                    @php
                                        $badge = match ($day['status']) {
                                            _lang('Present') => 'success',
                                            _lang('Absent') => 'danger',
                                            _lang('Late') => 'warning',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    @if ($day['status'])
                                        <span class="badge badge-light-{{ $badge }}">{{ $day['status'] }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                {{-- Manual/historical attendance never has a check-in/out time —
                                     shown as a plain dash rather than a fabricated value. --}}
                                <td>
                                    @if ($day['check_in_at'])
                                        {{ \Carbon\Carbon::parse($day['check_in_at'])->format('h:i A') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    @if ($day['check_out_at'])
                                        {{ \Carbon\Carbon::parse($day['check_out_at'])->format('h:i A') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
