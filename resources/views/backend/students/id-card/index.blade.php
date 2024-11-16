@extends('layouts.backend')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <span class="panel-title">{{ _lang('Students List') }}</span>
                    <div class="clearfix"></div>
                </div>
                <form id="studentCardForm" class="mt-3 params-panel validate" method="get" autocomplete="off"
                    accept-charset="utf-8" action="#">

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="control-label" for="class_id_select">{{ _lang('Class') }}</label>
                                <select name="class_id" id="class_id_select" class="form-control select2" required>
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {!! create_option('classes', 'id', 'class_name', request()->class_id) !!}
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Group') }}</label>
                                <select name="group_id" class="form-control select2">
                                    <option value="">{{ _lang('Select One') }}</option>
                                    {!! create_option('student_groups', 'id', 'group_name', request()->group_id) !!}
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Section') }}</label>
                                <select name="section_id" class="form-control select2">
                                    <option value="">{{ _lang('Select One') }}</option>

                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class=" control-label">{{ _lang('Card Validity Date') }}</label>

                                <input type="date" class="form-control" name="validity" required>

                            </div>
                        </div>
                    </div>


                    <div class="col-sm-3">
                        <div class="form-group">
                            <button type="submit" style="margin-top:24px;"
                                class="btn btn-primary btn-block rect-btn">{{ _lang('Search') }}</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
@section('js-script')
    <script>
        // Section options for this page's class_id select are populated by the
        // global cascade script (resources/views/partials/class_section_group_cascade.blade.php,
        // loaded via layouts.backend) — a second handler here duplicated that
        // same AJAX call on every class change, firing two requests and racing
        // to overwrite the same <select>.

        $('#studentCardForm').submit(function(e) {
            e.preventDefault();
            let data = {
                class_id: $('select[name=class_id]').val(),
                section_id: $('select[name=section_id]').val(),
                group_id: $('select[name=group_id]').val(),
                validity: $('input[name=validity]').val(),
                institute_id: "{{ auth()->user()->institute_id }}",
                branch_id: "{{ auth()->user()->branch_id }}"
            };

            let encoded = btoa(JSON.stringify(data));

            window.open('/student-list-for-card-print/' + encoded, '_blank');

        });
    </script>
@stop
