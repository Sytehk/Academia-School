@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Class Routine') }}
@endsection

@section('content')
    @include('parentmodule::partials.child_context')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                {{ _lang('Class Routine') }}
                @if ($data['class'])
                    — {{ $data['class']->class_name }}
                    @if ($data['section'])
                        ({{ $data['section']->section_name }})
                    @endif
                @endif
            </h3>
        </div>
        <div class="card-body">
            @if (!empty($data['routine']) && count($data['routine']))
                <div class="table-responsive">
                    <table class="table table-bordered routine-table align-middle text-center">
                        <tbody>
                            @foreach ($data['routine'] as $day => $periods)
                                <tr>
                                    <td class="fw-bold bg-light text-nowrap">{{ $day }}</td>
                                    @foreach ($periods as $field)
                                        @if ($field->start_time > 0)
                                            <td>
                                                <span class="fw-semibold d-block">{{ $field->subject_name }}</span>
                                                @if (!empty($field->teacher_name))
                                                    <span class="text-muted fs-8 d-block">{{ $field->teacher_name }}</span>
                                                @endif
                                                <span class="text-muted fs-8">{{ $field->start_time }} - {{ $field->end_time }}</span>
                                            </td>
                                        @else
                                            <td class="text-muted">—</td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-muted text-center py-10">{{ _lang('No class routine found.') }}</div>
            @endif
        </div>
    </div>
@endsection
