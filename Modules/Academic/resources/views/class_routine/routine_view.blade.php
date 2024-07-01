@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Class Routine') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card" data-collapsed="0">
                <div class="card-header">
                    <span class="card-title">
                        {{ _lang('Class Routine Of') }} {{ _lang('Class') }} <b>{{ $class->class_name }}</b> {{ _lang('Section') }} <b>{{ $section->section_name }}</b>
                    </span>
                    <button type="button" data-print="classic_routine" class="btn btn-primary btn-sm float-end print">
                        <i class="fa fa-print"></i> {{ _lang('Print Routine') }}
                    </button>
                </div>
                <div class="card-body">
                    <div class="col-lg-12">
                        <ul class="nav nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab"
                                    href="#modern">{{ _lang('Accordion View') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#classic">{{ _lang('Classic View') }}</a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div id="classic" class="tab-pane fade">
                                <table class="table table-bordered routine-table" id="classic_routine">
                                    <tbody>
                                        <tr>
                                            <td colspan="100" class="text-center">
                                                <span class="f-20">{{ get_option('school_name') }}</span><br>
                                                <span class="f-18">{{ _lang('Class Routine') }}</span><br>
                                                <span class="f-18">{{ _lang('Class') }}: {{ $class->class_name }}</span> |
                                                <span class="f-18">{{ _lang('Section') }}:
                                                    {{ $section->section_name }}</span>
                                            </td>
                                        </tr>
                                        @foreach ($routine as $key => $data)
                                            <tr>
                                                <td>{{ $key }}</td>
                                                @php
                                                    $i = 1;
                                                    $j = 1;
                                                @endphp

                                                @foreach ($data as $field)
                                                    @if ($field->start_time > 0)
                                                        <td>
                                                            {{ $field->subject_name }}<br>
                                                            @php
                                                                if ($field->teacher_id) {
                                                                    $teacher = Modules\Academic\Models\Teacher::where(
                                                                        'id',
                                                                        $field->teacher_id,
                                                                    )->first();
                                                                }
                                                            @endphp
                                                            {{ $teacher->name ?? '' }}
                                                            ({{ $field->start_time }} - {{ $field->end_time }})
                                                        </td>
                                                        @php($i++)
                                                    @endif

                                                    @if ($j == count($data))
                                                        @for ($l = $i; $l <= count($data); $l++)
                                                            <td>&nbsp;<br>&nbsp;<br>&nbsp;</td>
                                                        @endfor
                                                    @endif

                                                    @php($j++)
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div id="modern" class="tab-pane fade show active">
                                <div class="accordion" id="accordion">
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
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Subject</th>
                                                                <th>Teacher</th>
                                                                <th>Start Time</th>
                                                                <th>End Time</th>
                                                                <th>Room</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($data as $field)
                                                                @if ($field->start_time > 0)
                                                                    <tr>
                                                                        <td>{{ $field->subject_name }}</td>
                                                                        <td>{{ $field->teacher_name }}</td>
                                                                        <td>{{ $field->start_time }}</td>
                                                                        <td>{{ $field->end_time }}</td>
                                                                        <td>{{ $field->room }}</td>
                                                                    </tr>
                                                                @endif
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div> <!-- End tab content -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
