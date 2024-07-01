@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Academic/Session Year') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <form method="post" action="{{ route('academic-years.store') }}" data-disable-on-submit>
                @csrf

                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="control-label my-2">{{ _lang('Session Name') }}</label>
                        <input type="number" class="form-control" name="session" value="{{ old('session') }}"
                            placeholder="2025" required>
                        @error('session')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="form-group">
                        <label class="control-label my-2">{{ _lang('Academic Year') }}</label>
                        <input type="text" class="form-control year" name="year" value="{{ old('year') }}"
                            placeholder="2024-2025" required>
                        @error('year')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>


                <div class="form-group">
                    <div class="col-lg-12">
                        <button type="reset" class="btn btn-danger mt-4">{{ _lang('Reset') }}</button>
                        <button type="submit" class="btn btn-primary mt-4">{{ _lang('Save') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
