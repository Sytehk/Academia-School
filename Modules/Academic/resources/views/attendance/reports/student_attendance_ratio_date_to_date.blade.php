@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Dashboard') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <form action="{{ url('reports-student_attendance_date_to_date/view') }}" method="post">
                @csrf

                <div class="row my-2">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Class') }}</label>
                            <select name="class_id" class="form-select form-select-sm form-select-solid"
                                data-control="select2" onChange="getData(this.value);" required>
                                <option value="">{{ _lang('Select Class') }}</option>
                                {!! create_option('classes', 'id', 'class_name', $class_id) !!}
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Section') }}</label>
                            <select name="section_id" class="form-select form-select-sm form-select-solid"
                                data-control="select2" data-selected="{{ $section_id ?? '' }}" required>
                                <option value="">{{ _lang('Select Class First') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row my-2">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('From Date') }}</label>
                            <div class="input-group">
                                <input type="date" class="form-control" name="from_date"
                                    value="{{ old('from_date') ?? $from_date }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('To Date') }}</label>
                            <div class="input-group">
                                <input type="date" class="form-control" name="to_date"
                                    value="{{ date('Y-m-d') ?? $to_date }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row my-2">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('% Greater-than ') }} (Optional) </label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" name="percentage"
                                    value="{{ old('percentage') ?? $percentage }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 d-flex align-items-end custom-margin">
                        <div class="form-group w-100">
                            <button type="submit" class="btn btn-primary">{{ _lang('Search') }}</button>
                        </div>
                    </div>
                </div>
            </form>

            @if (isset($report_data))
                <div class="col-lg-12 params-panel" id="attendance">
                    {{-- TODO --}}
                    {{-- @if (isset($report_data))
                        <form action="{{ route('student-attendance-ratio-date-to-date-pdf-download') }}" method="post">
                            @csrf

                            <input type="hidden" name="class_id" value="{{ $class_id }}">
                            <input type="hidden" name="section_id" value="{{ $section_id }}">
                            <input type="hidden" name="from_date" value="{{ $from_date }}">
                            <input type="hidden" name="to_date" value="{{ $to_date }}">
                            <input type="hidden" name="percentage" value="{{ $percentage }}">

                            <button type="submit" style="margin-top:24px;" class="btn btn-primary btn-sm pull-right mb-5">
                                <i class="fa fa-print"></i>
                                {{ _lang('Print Report') }}
                            </button>
                        </form>
                    @endif --}}

                    <div class="card-header text-center">
                        <div class="card-title mx-auto">
                            {{ get_option('school_name') }}<br>
                            {{ _lang('Attendance For Class : ') }} {{ $class }}<br>
                            {{ _lang('Section :') . ' ' . $section }} <br>
                            {{ _lang('From Date') . ' ' . $from_date }} </br>
                            {{ _lang('To Date') . ' ' . $to_date }}</br></br>
                        </div>
                    </div>

                    <div class="table-responsive">
                        @if (!empty($report_data))
                            <table class="table align-middle table-row-dashed fs-6 gy-5">
                                <!--begin::Table head-->
                                <thead>
                                    <!--begin::Table row-->
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>{{ _lang('Student Name') }}</th>
                                        <th>{{ _lang('Roll') }}</th>
                                        <th>Present</th>
                                        <th>Absent</th>
                                        <th>Ratio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($report_data as $key => $value)
                                        @if ($report_data[$key]['ratio'] >= $percentage)
                                            <tr>
                                                <td>
                                                    {{ $students[$key]->first_name . ' ' . $students[$key]->last_name }}
                                                </td>
                                                <td>{{ $students[$key]->roll }}</td>
                                                <td>{{ $report_data[$key]['present'] }}
                                                </td>
                                                <td>{{ $report_data[$key]['absent'] }}
                                                </td>
                                                <td>{{ $report_data[$key]['ratio'] }} %
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <h4 class="text-center">{{ _lang('No Records Found !') }}</h4>
                        @endif
                    </div>

                </div>
            @endif
        </div>
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
