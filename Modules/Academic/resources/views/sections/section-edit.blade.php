@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Section') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Update Section') }}
                    </div>
                </div>
                <div class="">
                    <form action="{{ route('sections.update', $section->id) }}" autocomplete="off"
                        enctype="multipart/form-data" method="post">
                        @csrf
                        @method('PATCH')

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Section Name') }}</label>
                                <input type="text" class="form-control" name="section_name"
                                    value="{{ $section->section_name }}" placeholder="Section name" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">Class</label>
                                <select name="class_id" class="form-select form-select-sm form-select-solid"
                                    data-control="select2" required>
                                    <option value="">Select One</option>
                                    {!! create_option('classes', 'id', 'class_name', $section->class_id) !!}
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">Group Name</label>
                                <select name="student_group_id" class="form-control select2" required>
                                    <option value="">Select Group Name</option>
                                    {!! create_option('student_groups', 'id', 'group_name', $section->student_group_id) !!}
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Room No/Name') }}</label>
                                <input type="text" class="form-control" name="room_no" required
                                    value="{{ $section->room_no }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Update Section') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
