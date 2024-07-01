@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Subject') }}
@endsection

@section('create-button')
    <a href="{{ route('subjects.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add New Subject') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default" data-collapsed="0">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Add New Subject') }}
                    </div>
                </div>
                <div class="">
                    <form action="{{ route('subjects.store') }}" method="post">
                        @csrf

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Class') }}</label>
                                    <select name="class_id" class="form-select form-select-sm form-select-solid"
                                        data-control="select2" required>
                                        <option value="">{{ _lang('Select Class') }}</option>
                                        {!! create_option('classes', 'id', 'class_name', old('class_id')) !!}
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">Group</label>
                                    <select name="group_id" class="form-select form-select-sm form-select-solid"
                                        data-control="select2">
                                        <option value="">{{ _lang('Select Group') }}</option>
                                        {!! create_option('student_groups', 'id', 'group_name', old('group_id')) !!}
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Subject Name') }}</label>
                                    <input type="text" class="form-control" name="subject_name"
                                        value="{{ old('subject_name') }}" placeholder="Subject Name" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Subject Code') }}</label>
                                    <input type="text" class="form-control" name="subject_code"
                                        value="{{ old('subject_code') }}" placeholder="Subject Code">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Subject Short Form') }}</label>
                                    <input type="text" class="form-control" name="subject_short_form"
                                        value="{{ old('subject_short_form') }}" placeholder="Subject Short Form">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    {!! generate_select(
                                        'subject_type',
                                        get_subject_types(),
                                        _lang('Subject Type'),
                                        old('subject_type'),
                                        true,
                                        _lang('--Select Type--'),
                                        'form-control',
                                        null,
                                        null,
                                        false,
                                    ) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    {!! generate_select(
                                        'subject_type_form',
                                        get_subject_all_types(),
                                        _lang('Subject Type Form'),
                                        old('subject_type_form'),
                                        true,
                                        _lang('--Select Type--'),
                                        'form-control',
                                        null,
                                        null,
                                        false,
                                    ) !!}
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('SL No') }}</label>
                                    <input type="number" class="form-control" name="sl_no" value="{{ old('sl_no') }}"
                                        placeholder="sl no">
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Add Subject') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
