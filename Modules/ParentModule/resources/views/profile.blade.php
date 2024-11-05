@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Profile') }}
@endsection

@section('content')
    <div class="row g-5 g-xl-6">
        {{-- Parent --}}
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">{{ _lang('Parent Information') }}</h3>
                </div>
                <div class="card-body">
                    @php
                        $pUser = $parent?->user;
                        $pImg = $pUser && $pUser->image
                            ? asset('storage/users/' . $pUser->image)
                            : asset('backend/images/avatar.png');
                    @endphp
                    <div class="text-center mb-6">
                        <div class="symbol symbol-100px symbol-circle mb-3">
                            <img src="{{ $pImg }}" alt="{{ _lang('Parent') }}" />
                        </div>
                        <div class="fs-4 fw-bold text-gray-900">{{ $pUser->name ?? ($parent->parent_name ?? '—') }}</div>
                        <div class="text-muted fs-7">{{ $pUser->email ?? '' }}</div>
                    </div>
                    <table class="table table-row-dashed align-middle gy-3 mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted">{{ _lang('Phone') }}</td>
                                <td class="text-end fw-semibold">{{ $parent->parent_phone ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">{{ _lang('Profession') }}</td>
                                <td class="text-end fw-semibold">{{ $parent->parent_profession ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">{{ _lang('Present Address') }}</td>
                                <td class="text-end fw-semibold">{{ $parent->present_address ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">{{ _lang('Permanent Address') }}</td>
                                <td class="text-end fw-semibold">{{ $parent->permanent_address ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Active child --}}
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">{{ _lang('Selected Child') }}</h3>
                </div>
                <div class="card-body">
                    @php
                        $student = $child?->student;
                    @endphp
                    @if ($student)
                        @php
                            $sImg = optional($student->user)->image
                                ? asset('storage/users/' . $student->user->image)
                                : asset('backend/images/avatar.png');
                        @endphp
                        <div class="d-flex align-items-center mb-6">
                            <div class="symbol symbol-90px symbol-circle me-4">
                                <img src="{{ $sImg }}" alt="{{ _lang('Child') }}" />
                            </div>
                            <div>
                                <div class="fs-3 fw-bold text-gray-900">
                                    {{ trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) }}
                                </div>
                                <div class="text-muted fs-7">{{ optional($student->user)->email ?? '' }}</div>
                            </div>
                        </div>
                        <div class="row">
                            @php
                                $fields = [
                                    _lang('Class') => optional($child->class)->class_name,
                                    _lang('Section') => optional($child->section)->section_name,
                                    _lang('Roll') => $child->roll,
                                    _lang('Group') => optional($child->group)->group_name,
                                    _lang('Gender') => $student->gender ?? null,
                                    _lang('Phone') => optional($student->user)->phone ?? ($student->phone ?? null),
                                ];
                            @endphp
                            @foreach ($fields as $label => $value)
                                <div class="col-md-6 mb-4">
                                    <div class="text-muted fs-8">{{ $label }}</div>
                                    <div class="fw-semibold fs-6 text-gray-900">{{ $value ?: '—' }}</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        @include('parentmodule::partials.no_child')
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
