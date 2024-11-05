{{-- Compact summary of the active child, shown on every child-scoped page so the
     parent always knows whose data they are viewing. Expects $child (StudentSession). --}}
@php
    $__student = $child?->student;
    $__childImage = $__student ? optional($__student->user)->image : null;
    $__img = $__childImage
        ? asset('storage/users/' . $__childImage)
        : asset('backend/images/avatar.png');
@endphp

@if ($child && $__student)
    <div class="card mb-5 mb-xl-6">
        <div class="card-body d-flex flex-wrap align-items-center py-4">
            <div class="symbol symbol-60px symbol-circle me-4">
                <img src="{{ $__img }}" alt="{{ _lang('Child') }}" />
            </div>
            <div class="flex-grow-1 me-4">
                <span class="text-gray-900 fs-4 fw-bold d-block">
                    {{ trim(($__student->first_name ?? '') . ' ' . ($__student->last_name ?? '')) }}
                </span>
                <div class="text-muted fs-7">
                    <span class="me-3">{{ _lang('Class') }}:
                        <b>{{ optional($child->class)->class_name ?? '—' }}</b></span>
                    <span class="me-3">{{ _lang('Section') }}:
                        <b>{{ optional($child->section)->section_name ?? '—' }}</b></span>
                    <span>{{ _lang('Roll') }}: <b>{{ $child->roll ?? '—' }}</b></span>
                </div>
            </div>
        </div>
    </div>
@else
    @include('parentmodule::partials.no_child')
@endif
