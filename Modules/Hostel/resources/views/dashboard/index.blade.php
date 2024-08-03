@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Hostel Dashboard') }}
@endsection

@section('content')
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="mb-0">{{ $occupancyRate }}%</h3>
                    <span class="text-muted">{{ _lang('Occupancy Rate') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="mb-0">{{ $vacantBeds }} / {{ $totalBeds }}</h3>
                    <span class="text-muted">{{ _lang('Vacant Beds') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="mb-0">{{ $activeMembers }}</h3>
                    <span class="text-muted">{{ _lang('Active Residents') }}</span>
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
