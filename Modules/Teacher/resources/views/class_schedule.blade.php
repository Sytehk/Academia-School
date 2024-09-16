@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Dashboard') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default" data-collapsed="0">
                <div class="card-header">
                    <span class="card-title">
                        {{ _lang('My Class Schedule') }}
                    </span>
                    <button type="button" data-print="classic_routine" class="btn btn-primary btn-sm pull-right print"><i
                            class="fa fa-print"></i> {{ _lang('Print Class Schedule') }}</button>
                </div>
                <div class="">
                    <div class="col-lg-12">
                        <table class="table table-bordered routine-table" id="classic_routine"
                            style="width: 100%; font-size: 12px;">
                            <tbody>
                                <tr>
                                    <td colspan="2" class="text-center">
                                        <span class="f-20">{{ get_option('school_name') }}</span><br>
                                        <span class="f-18">{{ _lang('My Class Schedule') }}</span><br>
                                    </td>
                                </tr>
                                @php
                                    // Define an array containing the days of the week starting from Sunday to Thursday
                                    $daysOfWeek = [
                                        'SATURDAY',
                                        'SUNDAY',
                                        'MONDAY',
                                        'TUESDAY',
                                        'WEDNESDAY',
                                        'THURSDAY',
                                        'FRIDAY',
                                    ];
                                @endphp

                                @foreach ($daysOfWeek as $day)
                                    <tr>
                                        <td style="width: 10%; text-align:center;">
                                            <span style="display:block; margin: auto 0">{{ $day }}</span>
                                        </td>
                                        <td>
                                            @if (isset($routine[$day]))
                                                @foreach ($routine[$day] as $routineData)
                                                    <div
                                                        style="display: inline-block; margin:0; padding:0; margin-right: 10px; padding-right: 10px; border-right: 1px solid #ddd;">
                                                        {{ _lang('Teacher') }} - <b>{{ $routineData->teacher_name }}</b><br>
                                                        {{ _lang('Class') }} - {{ $routineData->section_name }}<br>
                                                        {{ _lang('Section') }} - {{ $routineData->class_name }}<br>
                                                        {{ _lang('Subject') }} -
                                                        <b>{{ $routineData->subject_name }}</b><br>
                                                        {{ _lang('Time') }} <b>{{ $routineData->start_time }} -
                                                            {{ $routineData->end_time }}</b><br>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </td>
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
