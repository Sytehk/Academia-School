@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Dashboard') }}
@endsection

@section('styles')
    <style>
        .setting-tab {
            background: azure;
            border-bottom: none;
        }

        .nav-tabs .nav-item.show .nav-link,
        .nav-tabs .nav-link.active {
            color: #fff;
            background-color: #5fa0eb;
            border-color: #5fa0eb #5fa0eb #fff;
        }

        .nav-tabs .nav-link:focus,
        .nav-tabs .nav-link:hover {
            color: #fff;
            background-color: #5fa0eb;
            border-color: #5fa0eb #5fa0eb #5fa0eb;
            isolation: isolate;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Send SMS (Person Wise)') }}
                    </div>
                </div>

                <div class="">
                    <ul class="nav nav-tabs setting-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#parent_class" role="tab"
                                aria-selected="true">{{ _lang('Parent(Class)') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#parent_section" role="tab"
                                aria-selected="false">{{ _lang('Parent(Section)') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#teacher" role="tab"
                                aria-selected="false">{{ _lang('Teacher') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#staff" role="tab"
                                aria-selected="false">{{ _lang('Staff') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#anyone" role="tab"
                                aria-selected="false">{{ _lang('Anyone') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#admin" role="tab"
                                aria-selected="false">{{ _lang('Admin') }}</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div id="parent_class" class="tab-pane fade show active" role="tabpanel">
                            <div class="shadow p-4 mt-4">
                                <form action="{{ url('sms-send') }}"method="post">
                                    @csrf
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="control-label my-2">{{ _lang('Select Class') }}</label>
                                                <select name="class_id" id='class-id-sms' class="form-select select2">

                                                    {!! create_option('classes', 'id', 'class_name', old('class_id')) !!}
                                                </select>
                                            </div>
                                        </div>
                                        <input type="hidden" name="user_type" value="Parent">
                                        @include('sms::sms_compose.sms-template-layout', [
                                            'type' => 'parent_class',
                                        ])
                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <button type="submit"
                                                    class="btn btn-primary mt-5">{{ _lang('Send SMS') }}</button>
                                            </div>
                                        </div>
                                        <div id="parent-class-div"></div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div id="parent_section" class="tab-pane fade" role="tabpanel">
                            <div class="shadow p-4 mt-4">
                                <form action="{{ url('sms-send') }}"method="post">
                                    @csrf
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="mb-3">
                                                <label class="control-label my-2">{{ _lang('Select Class') }}</label>
                                                <select name="class_id_section" id='class-id-section'
                                                    class="form-select select2" onchange="getSection();">

                                                    {!! create_option('classes', 'id', 'class_name', old('class_id')) !!}
                                                </select>
                                            </div>
                                        </div>
                                        <input type="hidden" name="user_type" value="Parent">
                                        <div class="col-sm-6">
                                            <div class="mb-3">
                                                <label class="control-label my-2">{{ _lang('Select Section') }}</label>
                                                <select name="section_id" onchange="get_students();" id="section_id_sms"
                                                    class="form-select select2">

                                                </select>
                                            </div>
                                        </div>
                                        @include('sms::sms_compose.sms-template-layout', [
                                            'type' => 'parent_section',
                                        ])
                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <button type="submit"
                                                    class="btn btn-primary mt-5">{{ _lang('Send SMS') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="parent-section-div"></div>
                                </form>
                            </div>
                        </div>
                        <div id="teacher" class="tab-pane fade" role="tabpanel">
                            <div class="shadow p-4 mt-4">
                                <form action="{{ url('sms-send') }}"method="post">
                                    @csrf
                                    <input type="hidden" name="user_type" value="Teacher">
                                    <div class="row">
                                        <div class="mb-3" style="margin: 10px!important">
                                            <button class="btn btn-success btn-sm" type="button"
                                                onclick="getUsers('Teacher')">Show All</button>
                                        </div>
                                        @include('sms::sms_compose.sms-template-layout', [
                                            'type' => 'teacher',
                                        ])
                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <button type="submit"
                                                    class="btn btn-primary mt-5">{{ _lang('Send SMS') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="Teacher-div"></div>
                                </form>
                            </div>
                        </div>
                        <div id="staff" class="tab-pane fade" role="tabpanel">
                            <div class="shadow p-4 mt-4">
                                <form action="{{ url('sms-send') }}"method="post">
                                    @csrf
                                    <div class="row">
                                        <div class="mb-3" style="margin: 10px!important">
                                            <button class="btn btn-success btn-sm" type="button"
                                                onclick="getUsers('Staff')">Show All</button>
                                        </div>
                                        <input type="hidden" name="user_type" value="Staff">
                                        @include('sms::sms_compose.sms-template-layout', [
                                            'type' => 'staff',
                                        ])
                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <button type="submit"
                                                    class="btn btn-primary mt-5">{{ _lang('Send SMS') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="Staff-div"></div>
                                </form>
                            </div>
                        </div>
                        <div id="admin" class="tab-pane fade" role="tabpanel">
                            <div class="shadow p-4 mt-4">
                                <form action="{{ url('sms-send') }}"method="post">
                                    @csrf
                                    <div class="row">
                                        <div class="mb-3" style="margin: 10px!important">
                                            <button class="btn btn-success btn-sm" type="button"
                                                onclick="getUsers('Admin')">Show All</button>
                                        </div>
                                        @include('sms::sms_compose.sms-template-layout', [
                                            'type' => 'admin',
                                        ])
                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <button type="submit"
                                                    class="btn btn-primary mt-5">{{ _lang('Send SMS') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="user_type" value="Admin">
                                    <div id="Admin-div"></div>
                                </form>
                            </div>
                        </div>
                        <div id="anyone" class="tab-pane fade" role="tabpanel">
                            <div class="shadow p-4 mt-4">
                                <form action="{{ url('sms-send') }}"method="post">
                                    @csrf
                                    <div class="row">
                                        <div class="col-lg-6 individual-number-show">
                                            <div class="mb-3">
                                                <label class="control-label my-2">{{ _lang('Number') }}</label>
                                                <input type="number" name="individual_number" id="individual-number"
                                                    class="form-control" />
                                            </div>
                                        </div>
                                        @include('sms::sms_compose.sms-template-layout', [
                                            'type' => 'anyone',
                                        ])
                                        <div class="col-lg-12">
                                            <div class="mb-3">
                                                <button type="submit"
                                                    class="btn btn-primary mt-5">{{ _lang('Send SMS') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
@section('js-script')
    <script>
        $('#class-id-sms').change(function() {
            var classId = $(this).val()
            console.log('id', classId)
            var link = "{{ url('students/get_students') }}" + '/' + classId
            console.log('link', link)
            // return 1;
            $.ajax({
                type: "get",
                url: link,
                classId: classId,
                success: function(data) {
                    console.log('data', data)
                    $('#parent-class-div').html(data);
                }
            });
        })

        function getSection() {
            if ($('select[name=class_id_section]').val() != "") {
                var _token = $('input[name=_token]').val();
                var class_id = $('select[name=class_id_section]').val();
                console.log('class_id', class_id)
                $.ajax({
                    type: "POST",
                    url: "{{ url('sections-section') }}",
                    data: {
                        _token: _token,
                        class_id: class_id
                    },
                    success: function(data) {
                        console.log('data --', data)
                        $("#section_id_sms").html(data);
                    }
                });
            }
        }

        function get_students() {
            var class_id = "/" + $('select[name=class_id_section]').val();
            var section_id = "/" + $('select[name=section_id]').val();
            var link = "{{ url('students/get_students/sms') }}" + class_id + section_id;
            console.log('class_id', class_id)
            console.log('section_id', section_id)
            console.log('link', link)
            console.log('link', link)
            $.ajax({
                type: "get",
                url: link,

                success: function(data) {
                    $('#parent-section-div').html(data);

                }
            });
        }

        function getUsers(type) {
            $.ajax({
                url: "{{ url('users-get_users') }}/" + type,
                success: function(data) {
                    return $('#' + type + '-div').html(data);

                }
            });
        }
        $('.parent_class').change(function() {
            var selectedOption = $(this).find(':selected');
            var templateDescription = selectedOption.data('template');
            $('#message_content_parent_class').val(templateDescription);

        });
        $('.parent_section').change(function() {
            var selectedOption = $(this).find(':selected');
            var templateDescription = selectedOption.data('template');
            $('#message_content_parent_section').val(templateDescription);

        });
        $('.teacher').change(function() {
            console.log('object :>> ');
            var selectedOption = $(this).find(':selected');
            var templateDescription = selectedOption.data('template');
            console.log('templateDescription', templateDescription);
            $('#message_content_teacher').val(templateDescription);

        });
        $('.staff').change(function() {

            var selectedOption = $(this).find(':selected');
            var templateDescription = selectedOption.data('template');
            $('#message_content_staff').val(templateDescription);

        });
        $('.anyone').change(function() {
            var selectedOption = $(this).find(':selected');
            var templateDescription = selectedOption.data('template');
            console.log('templateDescription :>> ', templateDescription);
            $('#message_content_anyone').val(templateDescription);

        });
        $('.admin').change(function() {
            var selectedOption = $(this).find(':selected');
            var templateDescription = selectedOption.data('template');
            $('#message_content_admin').val(templateDescription);
        });
    </script>
@stop
