@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Event') }}
@endsection
@section('create-button')
    <a class="btn btn-sm fw-bold btn-primary" href="{{ route('events.index') }}">
        {{ _lang('Events List') }}
    </a>
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <form method="post" action="{{ route('events.store') }}" enctype="multipart/form-data" data-disable-on-submit>
                @csrf

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Start Date') }}</label>
                            <input type="date" class="form-control" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}"
                                required>
                            @error('start_date')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('End Date') }}</label>
                            <input type="date" class="form-control" name="end_date" value="{{ old('end_date', date('Y-m-d')) }}" required>
                            @error('end_date')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Title') }}</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}"
                                placeholder="Title" required>
                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Details') }}</label>
                            <textarea class="form-control summernote" name="details" placeholder="Details" required>{{ old('details') }}</textarea>
                            @error('details')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Location') }}</label>
                            <input type="text" class="form-control" name="location" placeholder="Location"
                                value="{{ old('location') }}" required>
                            @error('location')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Image') }}</label>
                            <input type="file" class="form-control" name="image">
                            @error('image')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <button type="reset" class="btn btn-danger mt-4">{{ _lang('Reset') }}</button>
                            <button type="submit" class="btn btn-primary mt-4">{{ _lang('Save') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
