@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Dashboard') }}
@endsection
<style>
    .modal-backdrop {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: -999 !important;
        background-color: #000;
    }

    .td_text_center {
        text-align: center
    }
</style>

@section('content')
    <div class="row">
        <div class="card card-default">
            <div class="card-header">
                <span class="card-title">{{ _lang('Attendance Waiver') }}</span>
            </div>

            <div class="">
                <div class="row">

                    <div class="col-lg-12">
                        @if (\Session::has('success'))
                            <div class="alert alert-success">
                                <p>{{ \Session::get('success') }}</p>
                            </div>
                            <br />
                        @endif
                        <form method="get">
                            @csrf

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label my-2">{{ _lang('Class') }}</label>
                                        <select name="class_id" class="form-select form-select-sm form-select-solid"
                                            data-control="select2" onChange="getData(this.value);" required>
                                            <option value="">{{ _lang('Select Class') }}</option>
                                            {!! create_option('classes', 'id', 'class_name', $classId) !!}
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="control-label my-2">{{ _lang('Section') }}</label>
                                        <select name="section_id" class="form-select form-select-sm form-select-solid"
                                            data-control="select2" data-selected="{{ $sectionId ?? '' }}" required>
                                            <option value="">{{ _lang('Select Class First') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary mt-5">{{ _lang('Search') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    @if (isset($fines) && count($fines) > 0)
                        <div class="col-lg-12">
                            <div class="">
                                <form action="{{ route('attendance-waiver.store') }}" method="post">
                                    @csrf

                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>
                                                    <input type="checkbox" id="select-all">
                                                </th>
                                                <th>{{ _lang('Roll') }}</th>
                                                <th>{{ _lang('Name') }}</th>
                                                <th>{{ _lang('Lab Fine') }}</th>
                                                <th>{{ _lang('Quiz Fine') }}</th>
                                                <th>{{ _lang('Attd % Fine') }}</th>
                                                <th>{{ _lang('Waiver') }}</th>
                                                <th>{{ _lang('Total Fine') }}</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @php
                                                $sl = 1;
                                            @endphp
                                            @forelse ($fines as $data)
                                                @php
                                                    $attendanceWaiver = Modules\Finance\Models\AttendanceWaiver::where(
                                                        'student_id',
                                                        $data['student_id'],
                                                    )->first();

                                                @endphp

                                                @if (isset($attendanceWaiver) && $attendanceWaiver)
                                                    <tr style="padding: 15px 0 !important;">
                                                        <th scope="row">
                                                            <input type="checkbox" class="waiver-checkbox"
                                                                value="{{ $data['student_id'] }}" name="student_ids[]">
                                                        </th>
                                                        <td class="td_text_center">{{ $data['roll'] }}</td>
                                                        <td class="td_text_center">{{ $data['first_name'] }}</td>
                                                        <td class="td_text_center">
                                                            <label
                                                                style="display:block">{{ format_currency($data['labFine']) }}</label>
                                                            <input type="number" name="lab_fines[]"
                                                                value="{{ floatVal($attendanceWaiver->lab_fine ?? 0) }}">
                                                        </td>
                                                        <td class="td_text_center">
                                                            <label
                                                                style="display:block">{{ format_currency($data['quizFine']) }}</label>
                                                            <input type="number" name="quiz_fines[]"
                                                                value="{{ floatVal($attendanceWaiver->quiz_fine ?? 0) }}">
                                                        </td>
                                                        <td class="td_text_center">
                                                            <label
                                                                style="display:block">{{ format_currency($data['attendanceFine']) }}</label>
                                                            <input type="number" name="attendance_fines[]"
                                                                value="{{ floatVal($attendanceWaiver->attendance_fine ?? 0) }}">
                                                        </td>
                                                        <td class="td_text_center">
                                                            {{ floatVal($attendanceWaiver->lab_fine ?? 0) + floatVal($attendanceWaiver->quiz_fine ?? 0) + floatVal($attendanceWaiver->attendance_fine ?? 0) }}
                                                        </td>

                                                        <td class="td_text_center">
                                                            {{ format_currency($data['totalFine']) }}
                                                        </td>
                                                    </tr>
                                                @endif

                                            @empty
                                                <tr style="padding: 15px 0 !important;">
                                                    <td colspan="6" class="text-danger">No Student Found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>

                                    <button type="submit" class="btn btn-success mt-5">{{ _lang('Waiver') }}</button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js-script')
    <script>
        $(document).ready(function() {
            $("#select-all").click(function() {
                var isChecked = $(this).prop("checked");
                $(".waiver-checkbox").prop("checked", isChecked);
            });

            $(".waiver-checkbox").click(function() {
                var allChecked = $(".waiver-checkbox:checked").length === $(".waiver-checkbox").length;
                $("#select-all").prop("checked", allChecked);
            });
        });

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

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const inputs = document.querySelectorAll("input[type='number']");

            inputs.forEach(input => {
                input.addEventListener("input", function(event) {
                    const inputElement = event.target;
                    const labelElement = inputElement.previousElementSibling;
                    const labelValue = parseFloat(labelElement.textContent);
                    const inputValue = parseFloat(inputElement.value);

                    if (inputValue > labelValue) {
                        alert("Input value cannot be greater than the label value.");
                        inputElement.value = 0; // Reset to 0 or previous valid value
                    }
                });
            });
        });
    </script>
@endsection
