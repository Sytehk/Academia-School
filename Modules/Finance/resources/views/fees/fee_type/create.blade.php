@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Fee Type') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-default">
                <div class="card-header">{{ _lang('Add Fee Type') }}</div>

                <div class="">
                    <form method="post" action="{{ url('fee_types') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="control-label my-2">{{ _lang('Fee Type') }}</label>
                                <input type="text" class="form-control" name="fee_type" value="{{ old('fee_type') }}"
                                    required>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="control-label my-2">{{ _lang('Fee Code') }}</label>
                                <input type="text" class="form-control" name="fee_code" value="{{ old('fee_code') }}"
                                    required>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="control-label my-2">{{ _lang('Note') }}</label>
                                <textarea class="form-control" name="note">{{ old('note') }}</textarea>
                            </div>
                        </div>


                        <div class="form-group">
                            <div class="col-lg-12">
                                <button type="reset" class="btn btn-danger">{{ _lang('Reset') }}</button>
                                <button type="submit" class="btn btn-primary mt-4">{{ _lang('Save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
