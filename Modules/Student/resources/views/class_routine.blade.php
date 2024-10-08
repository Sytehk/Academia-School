@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Class Routine') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default" data-collapsed="0">
                <div class="card-header">
                    <span class="card-title">
                        {{ _lang('Class Routine Of') }} {!! _lang('Class') . ' <b>' . $class->class_name . '</b>' !!} {!! _lang('Section') . ' <b>' . $section->section_name . '</b>' !!}
                    </span>
                    <button type="button" data-print="classic_routine" class="btn btn-primary btn-sm float-end print">
                        <i class="fa fa-print"></i> {{ _lang('Print Routine') }}
                    </button>
                </div>
                <div class="">
                    <div class="col-lg-12">
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
                                                    {{ _lang('Teacher') }} - {{ $field->teacher_name }}<br>
                                                    {{ $field->start_time }} - {{ $field->end_time }}
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
                </div>
            </div>
        </div>
    </div>
@endsection
