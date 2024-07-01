@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Period') }}
@endsection
@section('create-button')
    <a href="{{ route('periods.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Periods') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-default">
                <div class="card-header">{{ _lang('Add Period') }}</div>
                <div class="">
                    <form method="post" action="{{ route('periods.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="control-label my-2">{{ _lang('Period') }}</label>
                                <input type="text" class="form-control" name="period" placeholder="Period"
                                    value="{{ old('period') }}" required>
                                @error('period')
                                    <span class="text-danger">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="control-label my-2">{{ _lang('Serial No') }}</label>
                                <input type="text" class="form-control" name="serial_no" placeholder="SL NO."
                                    value="{{ old('serial_no') }}">

                                @error('serial_no')
                                    <span class="text-danger">
                                        {{ $message }}
                                    </span>
                                @enderror
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
