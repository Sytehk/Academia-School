@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Exam Attendance') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <form action="{{ url('exams-attendance') }}" method="post" autocomplete="off">
                @csrf

                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="my-2 control-label">{{ _lang('Class') }}</label>
                            <select name="class_id" class="form-select form-select-sm form-select-solid"
                                data-control="select2" onChange="getData();" required>
                                <option value="">{{ _lang('Select Class') }}</option>
                                {!! create_option('classes', 'id', 'class_name', $class_id) !!}
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="my-2 control-label">{{ _lang('Section') }}</label>
                            <select name="section_id" class="form-select form-select-sm form-select-solid"
                                data-control="select2" data-selected="{{ $section_id ?? '' }}" required>
                                <option value="">{{ _lang('Select Class First') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="my-2 control-label">{{ _lang('Subject') }}</label>
                            <select name="subject_id" id="subject_id" class="form-select form-select-sm form-select-solid"
                                data-control="select2" onChange="getExam();" required>
                                {!! create_option('subjects', 'id', 'subject_name', $subject_id, ['class_id' => $class_id]) !!}
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="my-2 control-label">{{ _lang('Select Exam') }}</label>
                            <select class="form-select form-select-sm form-select-solid" data-control="select2"
                                name="exam_id" required>
                                {!! create_option('exams', 'id', 'name', $examId) !!}
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2">
                    <div class="form-group">
                        <button type="submit"
                            class="btn btn-primary btn-block rect-btn margin_top_24">{{ _lang('Next') }}</button>
                    </div>
                </div>
            </form>

            @if (!empty($attendance))
                <div class="col-lg-12" id="attendance">
                    <div class="text-center card-header">
                        <div class="card-title">
                            {{ _lang('Exam Attendance For Class') }} {{ get_class_name($class_id) }}<br>
                            {{ _lang('Section') . ': ' . get_section_name($section_id) }} <br>
                            {{ _lang('Subject') . ': ' . get_subject_name($subject_id) }} <br>
                            {{ date('d-M-Y') }}<br>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <form action="{{ url('exams-attendance-take') }}" method="post">
                            @csrf
                            <table class="table table-bordered">
                                <thead>
                                    <th>{{ _lang('Name') }}</th>
                                    <th>{{ _lang('Roll') }}</th>
                                    <th>
                                        <label class="c-container">{{ _lang('Present') }}
                                            <input type="checkbox" id="present_all" onclick="present(this)">
                                            <span class="checkmark"></span>
                                        </label>
                                    </th>
                                    <th><label class="c-container">{{ _lang('Absent') }}<input type="checkbox"
                                                id="absent_all" onclick="absent(this)"><span
                                                class="checkmark"></span></label></th>
                                </thead>
                                <tbody>
                                    <input type="hidden" name="exam_id" value="{{ $examId }}">
                                    <input type="hidden" name="subject_id" value="{{ $subject_id }}">
                                    <input type="hidden" name="class_id" value="{{ $class_id }}">
                                    <input type="hidden" name="section_id" value="{{ $section_id }}">
                                    <input type="hidden" name="date" value="{{ $examDate ?? date('Y-m-d') }}">

                                    @foreach ($attendance as $key => $data)
                                        <tr>
                                            <td>{{ $data->first_name . ' ' . $data->last_name }}</td>
                                            <input type="hidden" name="student_id[]" value="{{ $data->student_id }}">
                                            <td>{{ $data->roll }}</td>
                                            <td>
                                                <label class="c-container">
                                                    <input type="radio" name="attendance[{{ $data->id }}]"
                                                        value="1" @if ($data->attendance == 1) checked @endif
                                                        class="present">
                                                    <span class="checkmark"></span>
                                                </label>
                                            </td>

                                            <td>
                                                <label class="c-container">

                                                    <input type="radio" name="attendance[{{ $data->id }}]"
                                                        value="2" @if ($data->attendance == 2) checked @endif
                                                        class="absent">

                                                    <span class="checkmark"></span>

                                                </label>
                                            </td>
                                        </tr>
                                    @endforeach

                                    <tr>
                                        <td colspan="100"><button type="submit"
                                                class="btn btn-primary pull-right rect-btn">{{ _lang('Save Attendance') }}</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </form>

                    </div>
                </div>

            @endif
        </div>
    </div>
@endsection

@section('js-script')
    <script type="text/javascript">
        $(window).load(function() {
            getExam();
        });

        function getData() {
            var _token = $('input[name=_token]').val();
            var class_id = $('select[name=class_id]').val();
            $.ajax({
                type: "POST",
                url: "{{ url('sections-section') }}",
                data: {
                    _token: _token,
                    class_id: class_id
                },
                success: function(data) {
                    $('select[name=section_id]').html(data);
                }
            });
            getSubject();
        }

        function getSubject() {
            var _token = $('input[name=_token]').val();
            var class_id = $('select[name=class_id]').val();

            $.ajax({
                type: "POST",
                url: "{{ url('class-wise-subjects') }}",
                data: {
                    _token: _token,
                    class_id: class_id
                },

                success: function(data) {
                    $('select[name=subject_id]')
                        .html(data)
                        .val(null)
                        .trigger('change');
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
            $(".absent,.late,.present,#late_all,#absent_all").prop("checked", false);
            var checkboxes = document.querySelectorAll('.present');
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i] != source)
                    checkboxes[i].checked = source.checked;
            }
        }

        function absent(source) {
            $(".absent,.late,.present,#present_all,#late_all").prop("checked", false);
            var checkboxes = document.querySelectorAll('.absent');
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i] != source)
                    checkboxes[i].checked = source.checked;
            }
        }

        function late(source) {
            $(".absent,.late,.present,#present_all,#absent_all").prop("checked", false);
            var checkboxes = document.querySelectorAll('.late');
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i] != source)
                    checkboxes[i].checked = source.checked;
            }
        }
    </script>
@stop
