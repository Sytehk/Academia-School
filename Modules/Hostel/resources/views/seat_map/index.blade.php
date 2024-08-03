@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Hostel Seat Map') }}
@endsection

@section('content')
    <div class="card mb-4">
        <div class="card-body d-flex gap-4">
            <div><span class="badge bg-secondary">&nbsp;</span> {{ _lang('Vacant') }}</div>
            <div><span class="badge bg-danger">&nbsp;</span> {{ _lang('Occupied') }}</div>
            <div class="ms-auto">
                <b>{{ _lang('Occupancy') }}:</b> {{ $occupiedBeds }} / {{ $totalBeds }}
            </div>
        </div>
    </div>

    <div class="row g-3">
        @forelse ($rooms as $room)
            <div class="col-md-4 col-lg-3">
                <div class="card h-100">
                    <div class="card-header">
                        <div class="card-title mb-0">
                            {{ $room->room_number }}
                            <div class="text-muted fs-7">
                                {{ $room->hostel?->hostel_name ?? _lang('Global') }} —
                                {{ $room->hostelCategory?->standard }}
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            @forelse ($room->beds as $bed)
                                @php $occupant = $bed->roomMember; @endphp
                                <div class="p-2 rounded text-center text-white {{ $occupant ? 'bg-danger' : 'bg-secondary' }}"
                                    style="width: 64px; height: 64px; display:flex; flex-direction:column; justify-content:center;"
                                    title="{{ $occupant ? ($occupant->student?->user?->name ?? _lang('Occupied')) : _lang('Vacant') }}">
                                    <div class="fw-bold">{{ $bed->bed_number }}</div>
                                    <div class="fs-8 text-truncate">
                                        {{ $occupant ? \Illuminate\Support\Str::limit($occupant->student?->user?->name ?? '', 8) : _lang('Vacant') }}
                                    </div>
                                </div>
                            @empty
                                <span class="text-muted">{{ _lang('No beds configured') }}</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card"><div class="card-body text-center text-muted">{{ _lang('No rooms found.') }}</div></div>
            </div>
        @endforelse
    </div>
@endsection
