@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Events') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ _lang('Events & Calendar') }}</h3>
        </div>
        <div class="card-body">
            <div class="row g-4">
                @forelse ($events as $event)
                    <div class="col-md-6 col-xl-4">
                        <div class="border border-dashed rounded p-4 h-100">
                            <div class="d-flex align-items-center mb-3">
                                <span class="symbol symbol-40px me-3">
                                    <span class="symbol-label bg-light-success">
                                        <i class="fa-solid fa-calendar-day text-success"></i>
                                    </span>
                                </span>
                                <span class="fw-bold fs-5 text-gray-900">{{ $event->name }}</span>
                            </div>
                            <div class="text-muted fs-8 mb-2">
                                <i class="fa-regular fa-clock me-1"></i>
                                {{ \Carbon\Carbon::parse($event->start_date)->format('d M, Y') }}
                                @if ($event->end_date && $event->end_date != $event->start_date)
                                    – {{ \Carbon\Carbon::parse($event->end_date)->format('d M, Y') }}
                                @endif
                            </div>
                            @if ($event->location)
                                <div class="text-muted fs-8 mb-2">
                                    <i class="fa-solid fa-location-dot me-1"></i>{{ $event->location }}
                                </div>
                            @endif
                            @if ($event->details)
                                <div class="text-gray-700 fs-7">{{ \Illuminate\Support\Str::limit(strip_tags($event->details), 120) }}</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-10">{{ _lang('No events found.') }}</div>
                @endforelse
            </div>

            @if ($events->hasPages())
                <div class="mt-4">{{ $events->links() }}</div>
            @endif
        </div>
    </div>
@endsection
