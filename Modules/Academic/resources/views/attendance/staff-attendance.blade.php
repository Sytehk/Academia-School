@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Staffs Attendance') }}
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

    <form class="params-card" action="{{ url('staffs-attendance') }}" method="post">
        @csrf

        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Role') }}</label>

                    <select name="user_type" class="form-select form-select-sm form-select-solid" data-control="select2"
                        required>

                        <option value="">--- Select Role ---</option>

                        @foreach ($roleList as $role)
                            <option value="{{ $role->name }}" {{ $user_type == $role->name ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach

                    </select>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Date') }}</label>
                    <div class="input-group">
                        <input type="date" class="form-control" name="date" value="{{ $date ?? date('Y-m-d') }}"
                            required>
                    </div>
                </div>
            </div>
            <div class="col-sm-4 d-flex align-items-end custom-margin">
                <div class="form-group w-100">
                    <button type="submit" class="btn btn-primary">{{ _lang('Search') }}</button>
                </div>
            </div>
        </div>
    </form>

    @if (!empty($attendance) && count($attendance) > 0)
        <div class="col-lg-12" style="margin-top: 30px;">
            <div class="text-center card-heading">
                <div class="card-title">
                    <h2>{{ _lang('Attendance Details') }}</h2>
                    <strong>{{ date('d-M-Y', strtotime($date)) }}</strong>
                </div>
            </div>
            <form action="{{ url('staffs-attendance-save') }}" method="post">
                @csrf

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <th style="padding-left: 15px;">
                                {{ _lang('Name') }}
                            </th>
                            <th>
                                <label class="">{{ _lang('Present') }}
                                    <input type="checkbox" id="present_all" onclick="present(this)" class="form-check-input"
                                        style="padding: 10px; margin-left: 10px;">
                                    <span class="checkmark"></span>
                                </label>
                            </th>
                            <th>
                                <label class="">{{ _lang('Absent') }}
                                    <input type="checkbox" id="absent_all" onclick="absent(this)" class="form-check-input"
                                        style="padding: 10px; margin-left: 10px;">
                                    <span class="checkmark"></span>
                                </label>
                            </th>
                            <th style="text-align: center;">In Time</th>
                            <th style="text-align: center;">Out Time</th>
                        </thead>
                        <tbody>
                            @foreach ($attendance as $key => $data)
                                <tr>
                                    <td style="padding-left: 15px;">
                                        {{ $data->name }}
                                    </td>
                                    <input type="hidden" name="date" value="{{ $date }}">
                                    <input type="hidden" name="type" value="{{ $user_type }}">
                                    <input type="hidden" name="user_id[]" value="{{ $data->user_id }}">
                                    <td>
                                        <label class="">
                                            <input type="checkbox" name="attendance[{{ $key }}]"
                                                @if ($data->attendance == '1') checked @endif value="1"
                                                class="present form-check-input">
                                            <span class="checkmark"></span>
                                    </td>
                                    <td>
                                        <label class="">
                                            <input type="checkbox" name="attendance[{{ $key }}]"
                                                @if ($data->attendance == '2') checked @endif value="2"
                                                class="absent form-check-input">
                                            <span class="checkmark"></span>
                                    </td>

                                    <td>
                                        <input type="time" class="form-control timepicker" name="start_time[]"
                                            value="{{ $data->start_time ?? '09:00' }}">
                                    </td>
                                    <td>
                                        <input type="time" class="form-control timepicker" name="end_time[]"
                                            value="{{ $data->end_time ?? '17:00' }}">
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="100">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="w-50">
                                            @include('partials.notify-channel', ['notifyChannel' => 'none', 'label' => 'Notify via'])
                                        </div>
                                        <button type="submit"
                                            class="mt-5 btn btn-primary">{{ _lang('Save Attendance') }}</button>
                                    </div>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    @endif
@endsection

@section('js-script')
    <script type="text/javascript">
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
