@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Student Attendance Delete') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <form class="params-panel" action="{{ route('student_attendance_delete') }}" method="post">
                @csrf
                <div class="col-sm-4">
                    <div class="form-group">
                        <label class="my-2 control-label">{{ _lang('Class') }}</label>
                        <select name="class_id" class="form-select form-select-sm form-select-solid" data-control="select2"
                            onChange="getData(this.value);" required>
                            <option value="">{{ _lang('Select Class') }}</option>
                            {!! create_option('classes', 'id', 'class_name', $class_id) !!}

                        </select>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label class="my-2 control-label">{{ _lang('Section') }}</label>
                        <select name="section_id" class="form-select form-select-sm form-select-solid"
                            data-control="select2" data-selected="{{ $section_id ?? '' }}">
                            <option value="">{{ _lang('Select Class First') }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label class="my-2 control-label">{{ _lang('Period') }}</label>
                        <select name="period_id" class="form-select form-select-sm form-select-solid" data-control="select2"
                            required>
                            <option value="">{{ _lang('Select Period') }}</option>
                            @foreach ($periods->sortBy('serial_no') as $period)
                                <option {{ $period->id == $period_id ? 'selected' : '' }} value="{{ $period->id }}">
                                    {{ $period->period }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="date" class="control-label">Date</label>
                        <div class="input-group">
                            <input type="date" class="form-control" name="date" value="{{ $date ?? date('Y-m-d') }}"
                                required>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <button type="submit"
                            class="btn btn-primary btn-block rect-btn margin_top_24">{{ _lang('Search') }}</button>
                    </div>
                </div>
            </form>

            @if (isset($result) && count($result) > 0)
                <div class="">
                    <form action="{{ route('student_attendance_delete') }}" class="form-horizontal" method="post">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ $class_id }}">
                        <input type="hidden" name="section_id" value="{{ $section_id }}">
                        <input type="hidden" name="period_id" value="{{ $period_id }}">
                        <input type="hidden" name="date" value="{{ $date }}">
                        <input type="hidden" name="type" value="delete">

                        <div class="col-lg-12">
                            <div class="card card-default" data-collapsed="0">
                                <div class="card-header">
                                    <div class="card-title">
                                        {{ _lang('Delete Attendance report') }}
                                    </div>
                                </div>
                                <div class="mt-5 table-responsive">

                                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                                        <thead>
                                            <th>{{ _lang('Class') }}</th>
                                            <th>{{ _lang('Section') }}</th>
                                            <th>{{ _lang('Period') }}</th>
                                            <th>{{ _lang('Date') }}</th>
                                            <th>{{ _lang('Attendance') }}</th>
                                        </thead>
                                        <tbody>
                                            @foreach ($result as $resultSingle)
                                                <tr>
                                                    <td> {{ $resultSingle->class?->class_name }} </td>
                                                    <td> {{ $resultSingle->section?->section_name }} </td>
                                                    <td> {{ $resultSingle->period?->period }} </td>
                                                    <td> {{ $resultSingle->date }} </td>
                                                    <td>
                                                        @if ($resultSingle->attendance == 1)
                                                            <span class="text-success">Present</span>
                                                        @endif

                                                        @if ($resultSingle->attendance == 2)
                                                            <span class="text-danger">Absent</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>

                        <button type="submit"
                            class="ml-5 btn btn-danger pull-left">{{ _lang('Delete Attendance') }}</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('js-script')
    <script type="text/javascript">
        function getData(val) {
            var _token = $('input[name=_token]').val();
            var class_id = $('select[name=class_id]').val();
            $.ajax({
                type: "POST",
                url: "{{ url('sections-section') }}",
                data: {
                    _token: _token,
                    class_id: class_id
                },
                beforeSend: function() {
                    $("#preloader").css("display", "block");
                },
                success: function(sections) {
                    $("#preloader").css("display", "none");
                    $('select[name=section_id]').html(sections);
                }
            });
        }
    </script>
@stop
