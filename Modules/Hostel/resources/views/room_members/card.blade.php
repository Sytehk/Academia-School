@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Student Hostel Card') }}
@endsection

@section('content')
    <div class="d-flex justify-content-center">
        <div class="card" style="width: 350px; border: 2px solid #333;">
            <div class="card-body text-center">
                <h5 class="mb-1">{{ get_option('school_name') }}</h5>
                <p class="text-muted mb-3">{{ _lang('Hostel ID Card') }}</p>

                <p class="mb-1"><strong>{{ _lang('Name') }}:</strong> {{ $member->student?->user?->name ?? '-' }}</p>
                <p class="mb-1"><strong>{{ _lang('Hostel') }}:</strong> {{ $member->room?->hostel?->hostel_name ?? '-' }}</p>
                <p class="mb-1"><strong>{{ _lang('Room') }}:</strong> {{ $member->room?->room_number ?? '-' }}</p>
                <p class="mb-3"><strong>{{ _lang('Bed') }}:</strong> {{ $member->bed?->bed_number ?? '-' }}</p>

                <div class="my-3">
                    {!! QrCode::size(90)->generate('HOSTEL-'.$member->id) !!}
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-3 no-print">
        <button class="btn btn-primary" onclick="window.print()">{{ _lang('Print') }}</button>
    </div>
@endsection
