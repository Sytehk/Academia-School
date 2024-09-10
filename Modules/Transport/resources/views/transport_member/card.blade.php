@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Student Transport Card') }}
@endsection

@section('content')
    <div class="d-flex justify-content-center">
        <div class="card" style="width: 350px; border: 2px solid #333;">
            <div class="card-body text-center">
                <h5 class="mb-1">{{ get_option('school_name') }}</h5>
                <p class="text-muted mb-3">{{ _lang('Transport ID Card') }}</p>

                <p class="mb-1"><strong>{{ _lang('Name') }}:</strong> {{ $member->student?->user?->name ?? '-' }}</p>
                <p class="mb-1"><strong>{{ _lang('Transport ID') }}:</strong> {{ $member->transport_id }}</p>
                <p class="mb-1"><strong>{{ _lang('Route') }}:</strong> {{ $member->route?->route_name ?? '-' }}</p>
                <p class="mb-3"><strong>{{ _lang('Stop') }}:</strong> {{ $member->stop?->stop_name ?? '-' }}</p>

                <div class="my-3">
                    {!! QrCode::size(90)->generate($member->transport_id ?: (string) $member->id) !!}
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-3 no-print">
        <button class="btn btn-primary" onclick="window.print()">{{ _lang('Print') }}</button>
    </div>
@endsection
