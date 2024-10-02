@extends('layouts.backend')

@php
    $member = $member ?? null;
    $isEdit = (bool) $member;
    $selectedType = old('member_type', $member->member_type ?? 'Student');
@endphp

@section('page_title_breadcrumb')
    {{ $isEdit ? _lang('Update Member') : _lang('Member') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-default" data-collapsed="0">
                <div class="card-header">
                    <div class="card-title">
                        <i class="entypo-plus-circled"></i>{{ $isEdit ? _lang('Update Member') : _lang('Add New Member') }}
                    </div>
                </div>
                <div class="">
                    <div class="col-lg-12">
                        <form action="{{ $isEdit ? route('librarymembers.update', $member->id) : route('librarymembers.store') }}"
                            class="form-horizontal form-groups-bordered" id="library-member-form"
                            enctype="multipart/form-data" method="post">
                            @csrf
                            @if ($isEdit)
                                @method('PATCH')
                            @endif

                            <div class="form-group">
                                <div class="col-sm-12">
                                    <label class="control-label my-2">{{ _lang('Member Type') }}</label>
                                    <select name="member_type" id="member_type"
                                        class="form-select form-select-sm form-select-solid" data-control="select2"
                                        required>
                                        <option value="Student" @selected($selectedType === 'Student')>
                                            {{ _lang('Student') }}</option>
                                        <option value="Teacher" @selected($selectedType === 'Teacher')>
                                            {{ _lang('Teacher') }}</option>
                                        <option value="Staff" @selected($selectedType === 'Staff')>
                                            {{ _lang('Staff') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="student-fields" id="student-fields" @if ($selectedType !== 'Student') style="display: none;" @endif>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label my-2">{{ _lang('Class') }}</label>
                                        <select name="class_id" id="class_id"
                                            class="form-select form-select-sm form-select-solid" data-control="select2">
                                            <option value="">{{ _lang('Select Class') }}</option>
                                            {!! create_option('classes', 'id', 'class_name', $selectedClassId ?? old('class_id')) !!}
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label my-2">{{ _lang('Section') }}</label>
                                        <select name="section_id" id="section_id"
                                            class="form-select form-select-sm form-select-solid" data-control="select2"
                                            data-selected="{{ $selectedSectionId ?? old('section_id') }}">
                                            <option value="">{{ _lang('Select Class First') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label my-2">{{ _lang('Student') }}</label>
                                        <select name="student_user_id" id="student_user_id"
                                            class="form-select form-select-sm form-select-solid" data-control="select2">
                                            <option value="">{{ _lang('Select Section First') }}</option>
                                            @if ($isEdit && $selectedType === 'Student' && $member->user_id)
                                                <option value="{{ $member->user_id }}" selected>
                                                    {{ $member->user?->name }}</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="teacher-fields" id="teacher-fields" @if ($selectedType !== 'Teacher') style="display: none;" @endif>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label my-2">{{ _lang('Teacher') }}</label>
                                        <select name="teacher_user_id" id="teacher_user_id"
                                            class="form-select form-select-sm form-select-solid" data-control="select2">
                                            <option value="">{{ _lang('Select Teacher') }}</option>
                                            @foreach ($teachers as $data)
                                                <option value="{{ $data->user_id }}"
                                                    @selected(old('teacher_user_id', $member->user_id ?? '') == $data->user_id && $selectedType === 'Teacher')>
                                                    {{ $data->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="staff-fields" id="staff-fields" @if ($selectedType !== 'Staff') style="display: none;" @endif>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label my-2">{{ _lang('Staff') }}</label>
                                        <select name="staff_user_id" id="staff_user_id"
                                            class="form-select form-select-sm form-select-solid" data-control="select2">
                                            <option value="">{{ _lang('Select Staff') }}</option>
                                            @foreach ($staffs as $data)
                                                <option value="{{ $data->user_id }}"
                                                    @selected(old('staff_user_id', $member->user_id ?? '') == $data->user_id && $selectedType === 'Staff')>
                                                    {{ $data->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            @error('user_id')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                            @error('student_user_id')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                            @error('teacher_user_id')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                            @error('staff_user_id')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror

                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-primary mt-5">
                                    {{ $isEdit ? _lang('Update Member') : _lang('Add Member') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js-script')
    <script type="text/javascript">
        $('document').ready(function() {
            var studentUrl = "{{ url('librarymembers-student') }}";
            var csrf = "{{ csrf_token() }}";
            var selectedStudent = @json(old('student_user_id', $isEdit && $selectedType === 'Student' ? $member->user_id : ''));

            function toggleMemberFields() {
                var memberType = $('#member_type').val();

                $('.student-fields, .teacher-fields, .staff-fields').hide();
                $('#student_user_id, #class_id, #section_id, #teacher_user_id, #staff_user_id')
                    .prop('disabled', true).prop('required', false);

                if (memberType === 'Student') {
                    $('.student-fields').show();
                    $('#student_user_id, #class_id, #section_id').prop('disabled', false);
                    $('#student_user_id, #class_id, #section_id').prop('required', true);
                } else if (memberType === 'Teacher') {
                    $('.teacher-fields').show();
                    $('#teacher_user_id').prop('disabled', false).prop('required', true);
                } else {
                    $('.staff-fields').show();
                    $('#staff_user_id').prop('disabled', false).prop('required', true);
                }
            }

            function loadStudents(sectionId) {
                if (!sectionId) {
                    $('#student_user_id').html('<option value="">{{ _lang('Select Section First') }}</option>');
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: studentUrl,
                    data: {
                        _token: csrf,
                        section_id: sectionId
                    },
                    success: function(data) {
                        $('#student_user_id').html(data);
                        if (selectedStudent) {
                            $('#student_user_id').val(String(selectedStudent));
                        }
                    }
                });
            }

            $('#member_type').on('change', toggleMemberFields);
            toggleMemberFields();

            $(document).on('change', '#section_id', function() {
                selectedStudent = '';
                loadStudents($(this).val());
            });

            var initialSection = $('#section_id').val() || $('#section_id').data('selected');
            if ($('#member_type').val() === 'Student' && initialSection) {
                loadStudents(initialSection);
            }
        });
    </script>
@stop
