@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Picklist') }}
@endsection
@section('create-button')
    <a href="{{ route('picklists.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Picklist') }}
    </a>
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default">
                <div class="card-header">{{ _lang('Add Picklist') }}</div>

                <div class="">
                    <form method="post" action="{{ url('picklists') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Type') }}</label>
                                    <select name="type" class="form-select form-select-sm form-select-solid"
                                        data-control="select2" required>
                                        <option value="">{{ _lang('Select Type') }}</option>
                                        <option {{ old('type') == 'Religion' ? 'selected' : '' }}>Religion</option>
                                        <option {{ old('type') == 'Designation' ? 'selected' : '' }}>Designation</option>
                                        <option {{ old('type') == 'Department' ? 'selected' : '' }}>Department</option>
                                        <option {{ old('type') == 'Staff Designation' ? 'selected' : '' }}>Staff Designation</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Value') }}</label>
                                    <input type="text" class="form-control" name="value" value="{{ old('value') }}"
                                        placeholder="Picklist" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-lg-12">
                                <button type="reset" class="btn btn-danger mt-5">{{ _lang('Reset') }}</button>
                                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
