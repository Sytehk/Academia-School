@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Parent Dashboard') }}
@endsection

@section('content')
    @if (empty($data['has_child']))
        @include('parentmodule::partials.no_child')
    @else
        @php
            $child = $data['child'];
        @endphp
        @include('parentmodule::partials.child_context')

        {{-- KPI cards --}}
        <div class="row g-5 g-xl-6 mb-5">
            @php
                $cards = [
                    ['label' => _lang('Children'), 'value' => $data['total_children'], 'icon' => 'fa-children', 'bg' => 'primary'],
                    ['label' => _lang("This Month Present"), 'value' => $data['attendance']['present'], 'icon' => 'fa-user-check', 'bg' => 'success'],
                    ['label' => _lang('This Month Absent'), 'value' => $data['attendance']['absent'], 'icon' => 'fa-user-xmark', 'bg' => 'danger'],
                    ['label' => _lang('Attendance %'), 'value' => $data['attendance']['percentage'] . '%', 'icon' => 'fa-percent', 'bg' => 'info'],
                    ['label' => _lang('Pending Fees'), 'value' => format_currency($data['fees']['pending']), 'icon' => 'fa-file-invoice-dollar', 'bg' => 'warning'],
                    ['label' => _lang('Paid Fees'), 'value' => format_currency($data['fees']['paid']), 'icon' => 'fa-circle-check', 'bg' => 'success'],
                    ['label' => _lang('Upcoming Exams'), 'value' => $data['upcoming_exams'], 'icon' => 'fa-calendar-days', 'bg' => 'primary'],
                    ['label' => _lang('Homework'), 'value' => $data['homework'], 'icon' => 'fa-file-pen', 'bg' => 'info'],
                ];
            @endphp

            @foreach ($cards as $card)
                <div class="col-6 col-md-4 col-xl-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="symbol symbol-45px me-4">
                                <span class="symbol-label bg-light-{{ $card['bg'] }}">
                                    <i class="fa-solid {{ $card['icon'] }} fs-2 text-{{ $card['bg'] }}"></i>
                                </span>
                            </div>
                            <div>
                                <div class="fs-3 fw-bold text-gray-900">{{ $card['value'] }}</div>
                                <div class="fs-7 text-muted">{{ $card['label'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-5 g-xl-6">
            {{-- Quick links --}}
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title">{{ _lang('Quick Links') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-3">
                            @php
                                $links = [
                                    ['parent.attendance', 'fa-calendar-check', _lang('Attendance')],
                                    ['parent.class-routine', 'fa-table-cells', _lang('Class Routine')],
                                    ['parent.exam-results', 'fa-award', _lang('Exam Results')],
                                    ['parent.fees.unpaid', 'fa-file-invoice-dollar', _lang('Unpaid Fees')],
                                    ['parent.assignments', 'fa-file-pen', _lang('Assignments')],
                                    ['parent.library', 'fa-book-open-reader', _lang('Library')],
                                ];
                            @endphp
                            @foreach ($links as $link)
                                <a href="{{ route($link[0]) }}"
                                    class="btn btn-light-primary d-flex align-items-center justify-content-start">
                                    <i class="fa-solid {{ $link[1] }} me-3"></i>{{ $link[2] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent notices --}}
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title">{{ _lang('Recent Notices') }}</h3>
                        <div class="card-toolbar">
                            <a href="{{ route('parent.notices') }}" class="btn btn-sm btn-light">{{ _lang('View All') }}</a>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        @forelse ($data['notices'] as $notice)
                            <div class="d-flex align-items-start mb-4">
                                <span class="bullet bullet-vertical h-40px bg-primary me-3"></span>
                                <div>
                                    <a href="{{ route('parent.notices') }}"
                                        class="text-gray-900 fw-semibold fs-6 d-block">{{ $notice->title }}</a>
                                    <span class="text-muted fs-8">{{ \Carbon\Carbon::parse($notice->date)->format('d M, Y') }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted text-center py-5">{{ _lang('No notices found.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Upcoming events --}}
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title">{{ _lang('Upcoming Events') }}</h3>
                        <div class="card-toolbar">
                            <a href="{{ route('parent.events') }}" class="btn btn-sm btn-light">{{ _lang('View All') }}</a>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        @forelse ($data['events'] as $event)
                            <div class="d-flex align-items-start mb-4">
                                <span class="bullet bullet-vertical h-40px bg-success me-3"></span>
                                <div>
                                    <span class="text-gray-900 fw-semibold fs-6 d-block">{{ $event->name }}</span>
                                    <span class="text-muted fs-8">
                                        {{ \Carbon\Carbon::parse($event->start_date)->format('d M, Y') }}
                                        @if ($event->location)
                                            · {{ $event->location }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted text-center py-5">{{ _lang('No upcoming events.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
