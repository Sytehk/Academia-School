@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Migration') }}
@endsection

<style>
    .modal {
        top: 150px !important;
        margin-top: -200px;
    }

    .modal-backdrop {
        position: fixed;
        top: 10;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: -999 !important;
        background-color: #000;
    }
</style>

@section('content')
    <form action="{{ route('student-migration.store') }}" method="post">
        @csrf
        <div class="row">
            <div class="col-lg-4">
                <div class="form-group" id="class-select">
                    <label class="my-2 control-label">{{ _lang('Select Class') }}</label>
                    <select name="class_id" id='class-id' class="form-select form-select-sm form-select-solid"
                        data-control="select2" onchange="getSection();" required>
                        {!! create_option('classes', 'id', 'class_name', old('class_id')) !!}
                    </select>
                </div>
            </div>
            <div class="col-lg-4 student-section ">
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Select Section') }}</label>
                    <select name="section_id" id="section_id" class="form-select form-select-sm form-select-solid"
                        data-control="select2" onchange="get_students();" required>
                    </select>
                </div>
            </div>
            @if ($type == 'pushback')
                <div class="col-lg-4 d-none">
                    <div class="form-group">
                        <label class="my-2 control-label">{{ _lang('Migration Type') }}</label>
                        <select required name="type" class="form-select form-select-sm form-select-solid"
                            data-control="select2">
                            <option value="pushback" selected>Push Back</option>
                        </select>
                    </div>
                </div>
            @else
                <div class="col-lg-4">
                    <div class="form-group">
                        <label class="my-2 control-label">{{ _lang('Migration Type') }}</label>
                        <select required name="type" class="form-select form-select-sm form-select-solid"
                            data-control="select2">
                            <option value="">---Select----</option>
                            <option value="withmarit">With Merit Position</option>
                            <option value="withoutmerit">With Out Merit Position</option>
                        </select>
                    </div>
                </div>
            @endif
        </div>

        <div class="mt-5 row">
            <div class="m-auto card-header">
                <h2 class="text-center card-title">{{ _lang('User List') }}</h2>
            </div>
            <div class="card-body" id="user_list">
            </div>
        </div>

        <a href="#" class="px-8 py-4 btn btn-primary er fs-6" data-bs-toggle="modal"
            data-bs-target="#kt_modal_new_card">Process</a>
        <!--begin::Modal - New Card-->
        <div class="modal fade" id="kt_modal_new_card" tabindex="-1" aria-hidden="true">
            <!--begin::Modal dialog-->
            <div class="modal-dialog modal-dialog-centered mw-650px">
                <!--begin::Modal content-->
                <div class="modal-content">
                    <!--begin::Modal header-->
                    <div class="modal-header">
                        <!--begin::Modal title-->
                        <h2>Student Migration</h2>
                        <!--end::Modal title-->
                        <!--begin::Close-->
                        <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                            <!--begin::Svg Icon | path: icons/duotune/arrows/arr061.svg-->
                            <span class="svg-icon svg-icon-1">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                        transform="rotate(-45 6 17.3137)" fill="currentColor" />
                                    <rect x="7.41422" y="6" width="16" height="2" rx="1"
                                        transform="rotate(45 7.41422 6)" fill="currentColor" />
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                        </div>
                        <!--end::Close-->
                    </div>
                    <!--end::Modal header-->
                    <!--begin::Modal body-->
                    <div class="modal-body scroll-y">
                        <!--begin::Form-->
                        <form id="kt_modal_new_card_form" class="form" action="#">
                            <div class="modal-body">
                                <div class="col-lg-12 ">
                                    <div class="form-group">
                                        <label class="my-2 control-label">{{ _lang('Year ') }}</label>
                                        <select class="form-select form-select-sm form-select-solid" data-control="select2"
                                            name="academic_year" required>
                                            {!! create_option('academic_years', 'id', 'session', get_option('academic_year')) !!}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group" id="class-select">
                                        <label class="my-2 control-label">{{ _lang('Select Class') }}</label>
                                        <select name="migrate_class_id" id='class-id'
                                            class="form-select form-select-sm form-select-solid" data-control="select2"
                                            required onchange="getMigrateSection();">
                                            {!! create_option('classes', 'id', 'class_name', old('migrate_class_id')) !!}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12 ">
                                    <div class="form-group">
                                        <label class="my-2 control-label">{{ _lang('Group ') }}</label>
                                        <select name="group_id" id="group"
                                            class="form-select form-select-sm form-select-solid" data-control="select2">
                                            {!! create_option('student_groups', 'id', 'group_name', old('group')) !!}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12 student-section ">
                                    <div class="form-group">
                                        <label class="my-2 control-label">{{ _lang('Select Section') }}</label>
                                        <select name="migrate_section_id" id="section_id" required
                                            class="form-select form-select-sm form-select-solid" data-control="select2">

                                        </select>
                                    </div>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="submit" id="kt_modal_new_card_submit"
                                    class="btn btn-primary">{{ _lang('Submit') }}</button>
                                <button type="reset" id="kt_modal_new_card_cancel"
                                    class="btn btn-light me-3">Close</button>
                            </div>
                        </form>
                        <!--end::Form-->
                    </div>
                    <!--end::Modal body-->
                </div>
                <!--end::Modal content-->
            </div>
            <!--end::Modal dialog-->
        </div>
        <!--end::Modal - New Card-->

    </form>
@endsection

@section('js-script')
    <script type="text/javascript">
        $(document).ready(function() {
            // Auto trigger if only 1 class exists
            if ($('#class-id option').length === 2) { // 1 option + placeholder
                $('#class-id').val($('#class-id option:eq(1)').val()).trigger('change');
            }

            // After section loaded → auto trigger student fetch
            $(document).on('change', '#section_id', function() {
                get_students();
            });

        });

        // Disable process button if no students
        $(document).on('change', '.student-checkbox', function() {
            let count = $('.student-checkbox:checked').length;
            $('.btn-primary[data-bs-target="#kt_modal_new_card"]').prop('disabled', count === 0);
        })

        function getSection() {
            let class_id = $('select[name=class_id]').val();

            if (!class_id) return;

            $.ajax({
                type: "POST",
                url: "{{ url('sections-section') }}",
                data: {
                    _token: $('input[name=_token]').val(),
                    class_id: class_id
                },
                success: function(data) {
                    $('select[name=section_id]').html(data);

                    // ✅ AUTO trigger if only one section
                    if ($('#section_id option').length === 2) {
                        $('#section_id').val($('#section_id option:eq(1)').val()).trigger('change');
                    }
                }
            });
        }

        function getMigrateSection() {

            if ($('select[name=migrate_class_id]').val() != "") {
                var _token = $('input[name=_token]').val();
                var class_id = $('select[name=migrate_class_id]').val();
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
                    success: function(data) {
                        $("#preloader").css("display", "none");
                        $('select[name=migrate_section_id]').html(data);
                    }
                });
            }
        }

        function get_students() {
            var class_id = "/" + $('select[name=class_id]').val();
            var section_id = "/" + $('select[name=section_id]').val();
            var link = "{{ url('students-get_students-migration') }}" + class_id + section_id;
            console.log('link', link)
            $.ajax({
                type: "get",
                url: link,
                success: function(data) {
                    $('#user_list').html(data);
                }
            });

        }
    </script>

    <script>
        $(document).ready(function() {
            $(document).on("click", "#student-select-all", function() {
                var isChecked = $(this).prop("checked");
                $(".student-checkbox").prop("checked", isChecked);
            });

            $(document).on("click", ".student-checkbox", function() {
                var allChecked = $(".student-checkbox:checked").length === $(".student-checkbox").length;
                $("#student-select-all").prop("checked", allChecked);
            });
        });
    </script>
@stop
