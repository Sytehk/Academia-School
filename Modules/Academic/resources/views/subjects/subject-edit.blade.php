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
                        {{ _lang('Edit Subject') }}
                    </div>
                </div>
                <div class="">
                    <form action="{{ route('subjects.update', $subject->id) }}" method="post">
                        @csrf
                        @method('PATCH')

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">Class</label>
                                    <select name="class_id" class="form-select form-select-sm form-select-solid"
                                        data-control="select2" required>

                                        {!! create_option('classes', 'id', 'class_name', $subject->class_id) !!}
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">Group</label>
                                    <select name="group_id" class="form-select form-select-sm form-select-solid"
                                        data-control="select2">
                                        <option value="">Select Group</option>
                                        <option value="1" @if ('1' == $subject->group_id) selected @endif>Science
                                        </option>
                                        <option value="2" @if ('2' == $subject->group_id) selected @endif>Business
                                            Studies</option>
                                        <option value="3" @if ('3' == $subject->group_id) selected @endif>Humanities
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Subject Name') }}</label>
                                    <input type="text" class="form-control" name="subject_name"
                                        value="{{ $subject->subject_name }}" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Subject Code') }}</label>
                                    <input type="text" class="form-control" name="subject_code"
                                        value="{{ $subject->subject_code }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Subject Short Form') }}</label>
                                    <input type="text" class="form-control" name="subject_short_form"
                                        value="{{ $subject->subject_short_form }}">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    {!! generate_select(
                                        'subject_type',
                                        get_subject_types(),
                                        _lang('Subject Type'),
                                        $subject->subject_type,
                                        true,
                                        _lang('--Select Type--'),
                                        'form-control',
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
                                        $subject->subject_type_form,
                                        true,
                                        _lang('--Select Type--'),
                                        'form-control',
                                    ) !!}
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('SL No') }}</label>
                                    <input type="number" class="form-control" name="sl_no" value="{{ $subject->sl_no }}"
                                        placeholder="sl_no">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-lg-12">
                                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Update Subject') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
