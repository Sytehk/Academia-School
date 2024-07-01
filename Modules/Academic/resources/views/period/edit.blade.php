@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Period') }}
@endsection
@section('create-button')
    <a href="{{ route('periods.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add New Period') }}
    </a>
@endsection


@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-default">
                <div class="card-header">{{ _lang('Update Period') }}</div>

                <div class="">
                    <form method="post" action="{{ route('periods.update', $period->id) }}">
                        @csrf

                        @method('PATCH')

                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="control-label my-2">{{ _lang('Period') }}</label>
                                <input type="text" class="form-control" name="period" placeholder="Period"
                                    value="{{ $period->period }}" required>

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
                                <input type="text" class="form-control" placeholder="SL NO." name="serial_no"
                                    value="{{ $period->serial_no }}">

                                @error('serial_no')
                                    <span class="text-danger">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-lg-12">
                                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Update') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
