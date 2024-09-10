@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Transport Dashboard') }}
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="mb-0">{{ $activeBuses }}</h3>
                    <span class="text-muted">{{ _lang('Active Buses') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="mb-0">{{ $studentsAssigned }}</h3>
                    <span class="text-muted">{{ _lang('Students Assigned') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="mb-0">{{ $vacantSeats }} / {{ $totalSeats }}</h3>
                    <span class="text-muted">{{ _lang('Vacant Seats') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h3 class="mb-0 text-success">{{ format_currency($todayCollection) }}</h3>
                    <span class="text-muted">{{ _lang("Today's Collection") }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <h3 class="mb-0 text-warning">{{ format_currency($totalDue) }}</h3>
                    <span class="text-muted">{{ _lang('Total Due') }}</span>
                </div>
            </div>
        </div>
    </div>
@endsection
