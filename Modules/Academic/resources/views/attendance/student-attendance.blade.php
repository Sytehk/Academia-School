@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Student Attendance') }}
@endsection
@section('content')
    <style>
        .table {
            background-color: #ffffff;
        }

        .table thead th {
            background-color: #f1f5f9;
            /* light gray */
            color: #1e293b;
            /* dark text */
            font-weight: 600;
            border: 1px solid #dee2e6;
        }

        .table tbody td {
            background-color: #ffffff;
            color: #1e293b;
            border: 1px solid #dee2e6;
        }

        /* Checkbox visibility fix */
        .form-check-input {
            width: 18px;
            height: 18px;
            border: 2px solid #0d6efd;
            /* Bootstrap primary */
            cursor: pointer;
        }

        /* Checked checkbox */
        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        /* Hover effect */
        .form-check-input:hover {
            border-color: #0a58ca;
        }

        /* Table hover */
        .table tbody tr:hover {
            background-color: #f8fafc;
        }
    </style>

    <div class="row">
        <div class="col-lg-12">
            <form class="params-panel" action="{{ url('student-attendance') }}" method="post">
                @csrf

                <div class="my-2 row">
                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label class="my-2 control-label">{{ _lang('Class') }}</label>
                            <select name="class_id" id="class_id_select" class="form-select form-select-sm form-select-solid"
                                data-control="select2" onChange="getData(this.value);" required>
                                <option value="">--Select Class--</option>
                                {!! create_option('classes', 'id', 'class_name', $class_id) !!}
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label class="my-2 control-label">{{ _lang('Section') }}</label>
                            <select name="section_id" id="section_id_select" class="form-select form-select-sm form-select-solid"
                                data-control="select2" data-selected="{{ $section_id ?? '' }}">
                                <option value="">{{ _lang('Select Class First') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- <div class="my-2 row">
                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label class="my-2 control-label">{{ _lang('Period') }}</label>
                            <select name="period_id" class="form-select form-select-sm form-select-solid"
                                data-control="select2">
                                <option value="">{{ _lang('Select Period') }}</option>
                                @foreach ($periods->sortBy('serial_no') as $period)
                                    <option {{ $period->id == $period_id ? 'selected' : '' }} value="{{ $period->id }}">
                                        {{ $period->period }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label class="my-2 control-label">{{ _lang('Subject') }}</label>
                            <select name="subject_id" class="form-select form-select-sm form-select-solid"
                                data-control="select2">
                                <option value="">Select Subject</option>
                                @foreach ($subjects as $subject)
                                    <option {{ $subject->id == $subject_id ? 'selected' : '' }}
                                        value="{{ $subject->id }}">
                                        {{ $subject->subject_name }} - {{ $subject->subject_code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div> --}}

                <div class="my-2 row">
                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label class="my-2 control-label">Date</label>
                            <div class="input-group">
                                <input type="date" class="form-control" name="date"
                                    value="{{ $date ?? date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6 d-flex align-items-end custom-margin">
                        <div class="form-group w-100">
                            <button type="submit" class="btn btn-primary">{{ _lang('Search') }}</button>
                        </div>
                    </div>

                </div>
            </form>

            @if (!empty($attendance))

                <div class="col-lg-12" id="attendance">
                    <div class="text-center card-header">
                        <div class="mx-auto card-title">
                            {{ get_option('school_name') }}<br>
                            {{ _lang('Attendance For Class : ') }} {{ $class }}<br>
                            {{ _lang('Section :') . ' ' . $section }} <br>
                            {{ _lang('Period : ') . ' ' . $periodName }} <br>
                            {{ _lang('Date : ') . ' ' . date('d-M-Y', strtotime($date)) }}<br>
                        </div>
                    </div>

                    <form action="{{ url('student-attendance-save') }}" method="post">
                        @csrf

                        <div class="table-responsive">
                        </div>
                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                            <!--begin::Table head-->
                            <thead>
                                <!--begin::Table row-->
                                <tr class="text-gray-400 text-start fw-bold fs-7 text-uppercase gs-0">
                                    <th style="padding-left: 15px;">{{ _lang('Admission Number') }}</th>
                                    <th>{{ _lang('Roll NO.') }}</th>
                                    <th>{{ _lang('Name') }}</th>
                                    <th>
                                        <label class="">
                                            {{ _lang('Present') }}
                                            <input class="form-check-input" type="checkbox" id="present_all"
                                                onclick="present(this)" style="padding: 10px; margin-left: 10px;">
                                            <span class="checkmark"></span>
                                        </label>
                                    </th>
                                    <th>
                                        <label class="">{{ _lang('Absent') }}
                                            <input class="form-check-input" type="checkbox" id="absent_all"
                                                onclick="absent(this)" style="padding: 10px; margin-left: 10px;">
                                            <span class="checkmark"></span>
                                        </label>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($attendance as $key => $data)
                                    <tr>
                                        <td style="padding-left: 15px;">
                                            {{ $data->qr_code }}
                                        </td>

                                        <td>
                                            {{ $data->roll_no }}
                                        </td>
                                        <td>
                                            @if ($data->image != null)
                                                <img src="{{ $data->image ? asset('storage/users/' . $data->image) : asset('assets/media/svg/files/blank-image.svg') }}"
                                                    class="w-75px me-3" alt="">
                                            @else
                                                <img src="{{ asset('images/no-image.png') }}" class="w-75px me-3">
                                            @endif
                                            {{ $data->first_name . ' ' . $data->last_name }}
                                        </td>
                                        <input type="hidden" name="student_ids[]" value="{{ $data->id }}">
                                        <td>
                                            <label class="">
                                                <input type="checkbox" name="attendance[{{ $key }}]"
                                                    @if ($data->attendance == '1') checked @endif value="1"
                                                    class="present form-check-input">
                                                <span class="checkmark"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <label class="">
                                                <input type="checkbox" name="attendance[{{ $key }}]"
                                                    @if ($data->attendance == '2') checked @endif value="2"
                                                    class="absent form-check-input">
                                                <span class="checkmark"></span>
                                            </label>
                                        </td>
                                    </tr>
                                @endforeach

                                <input type="hidden" name="class_id" value="{{ $class_id }}">
                                <input type="hidden" name="section_id" value="{{ $section_id }}">
                                <input type="hidden" name="period_id" value="{{ $period_id }}">
                                <input type="hidden" name="subject_id" value="{{ $subject_id }}">
                                <input type="hidden" name="date" value="{{ $date }}">
                                <tr>
                                    <td colspan="100">
                                        <div class="d-flex justify-content-between align-items-center"
                                            style="padding-left: 15px;">
                                            <div class="w-50">
                                                @include('partials.notify-channel', ['notifyChannel' => 'none', 'label' => 'Notify via'])
                                            </div>
                                            <button type="submit"
                                                class="mt-5 btn btn-primary">{{ _lang('Take Attendance') }}</button>
                                        </div>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
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

            if (!class_id) {
                $('select[name=section_id]').html('<option value="">{{ _lang('Select Class First') }}</option>');
                return;
            }

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

            $.ajax({
                type: "POST",
                url: "{{ url('class-wise-subjects') }}",
                data: {
                    _token: _token,
                    class_id: class_id
                },
                beforeSend: function() {
                    $("#preloader").css("display", "block");
                },
                success: function(sections) {
                    $("#preloader").css("display", "none");
                    $('select[name=subject_id]').html(sections);
                }
            });
        }

        $("input:checkbox").on('click', function() {

            var $box = $(this);
            if ($box.is(":checked")) {
                var group = "input:checkbox[name='" + $box.attr("name") + "']";
                $(group).prop("checked", false);
                $box.prop("checked", true);
            } else {
                $box.prop("checked", false);
            }
        });

        function present(source) {
            $(".absent,.late,.present,.holiday,#late_all,#absent_all,#holiday_all").prop("checked", false);
            var checkboxes = document.querySelectorAll('.present');
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i] != source)
                    checkboxes[i].checked = source.checked;
            }
        }

        function absent(source) {
            $(".absent,.late,.present,.holiday,#present_all,#late_all,#holiday_all").prop("checked", false);
            var checkboxes = document.querySelectorAll('.absent');
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i] != source)
                    checkboxes[i].checked = source.checked;
            }
        }

        function late(source) {
            $(".absent,.late,.present,.holiday,#present_all,#absent_all,#holiday_all").prop("checked", false);
            var checkboxes = document.querySelectorAll('.late');
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i] != source)
                    checkboxes[i].checked = source.checked;
            }
        }

        function holiday(source) {
            $(".absent,.late,.present,.holiday,#present_all,#absent_all").prop("checked", false);
            var checkboxes = document.querySelectorAll('.holiday');
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i] != source)
                    checkboxes[i].checked = source.checked;
            }
        }
    </script>
@stop
