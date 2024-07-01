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
            <form method="post" action="{{ route('events.update', $id) }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Start Date') }}</label>
                            <input type="text" class="form-control datetimepicker" name="start_date"
                                value="{{ $event->start_date }}" required>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('End Date') }}</label>
                            <input type="text" class="form-control datetimepicker" name="end_date"
                                value="{{ $event->end_date }}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Name') }}</label>
                            <input type="text" class="form-control" name="name" value="{{ $event->name }}" required>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Details') }}</label>
                            <textarea class="form-control summernote" name="details" required>{{ $event->details }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Location') }}</label>
                            <input type="text" class="form-control" name="location" value="{{ $event->location }}"
                                required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">

                            @if ($event->image)
                                <img src="{{ $event->image ? asset('storage/events/' . $event->image) : asset('assets/media/svg/files/blank-image.svg') }}"
                                    class="me-3 my-5" alt="" width="50px">
                            @endif

                            <label class="control-label my-2">{{ _lang('Image') }}</label>
                            <input type="file" class="form-control" name="image">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary mt-5">{{ _lang('Update') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
