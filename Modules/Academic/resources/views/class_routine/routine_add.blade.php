@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Class Routine') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card" data-collapsed="0">
                <div class="card-header">
                    <h5 class="card-title">
                        {{ _lang('Class') }} : <b>{{ $class->class_name }}</b>
                        <span class="float-end">{{ _lang('Section') }} : <b>{{ $section->section_name }}</b></span>
                    </h5>
                </div>

                <div class="card-body">
                    <div class="accordion" id="accordion">
                        @if (!empty($routine))
                            @foreach ($routine as $key => $data)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading-{{ $key }}">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse-{{ $key }}" aria-expanded="false"
                                            aria-controls="collapse-{{ $key }}">
                                            {{ $key }}
                                        </button>
                                    </h2>
                                    <div id="collapse-{{ $key }}" class="accordion-collapse collapse"
                                        aria-labelledby="heading-{{ $key }}" data-bs-parent="#accordion">
                                        <div class="accordion-body">
                                            <form action="{{ url('class-routines-store') }}" method="post"
                                                autocomplete="off">
                                                @csrf
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Subject</th>
                                                            <th>Start Time</th>
                                                            <th>End Time</th>
                                                            <th>Assign Teacher</th>
                                                            <th>Room</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($data as $field)
                                                            <tr>
                                                                <td>{{ $field['subject']->subject_name }}</td>
                                                                <input type="hidden" name="class_id[]"
                                                                    value="{{ $class->id }}">
                                                                <input type="hidden" name="section_id[]"
                                                                    value="{{ $section->id }}">
                                                                <input type="hidden" name="subject_id[]"
                                                                    value="{{ $field['subject']->s_id }}">
                                                                <input type="hidden" name="routine_id[]"
                                                                    value="{{ $field['routine']->id ?? '' }}">
                                                                <input type="hidden" name="day[]"
                                                                    value="{{ $key }}">
                                                                <td>
                                                                    <input type="time" class="form-control"
                                                                        name="start_time[]"
                                                                        value="{{ $field['routine']->start_time ?? '10:00' }}">
                                                                </td>
                                                                <td>
                                                                    <input type="time" class="form-control"
                                                                        name="end_time[]"
                                                                        value="{{ $field['routine']->end_time ?? '16:00' }}">
                                                                </td>
                                                                <td>
                                                                    <select name="teacher_id[]" class="form-select"
                                                                        data-control="select2">
                                                                        <option value="">{{ _lang('Select One') }}
                                                                        </option>
                                                                        {!! create_option('teachers', 'id', 'name', $field['routine']->teacher_id ?? null) !!}
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control"
                                                                        name="room[]" placeholder="{{ _lang('Room') }}"
                                                                        value="{{ $field['routine']->room ?? '' }}">
                                                                </td>
                                                            </tr>
                                                        @endforeach

                                                        <tr>
                                                            <td colspan="3">
                                                                <button type="submit" class="btn btn-primary float-end">
                                                                    Save {{ ucwords(strtolower($key)) }} Routine
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p>No routines available for this class and section.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
