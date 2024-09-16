@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ ucfirst('prayer') }}
@endsection

@section('create-button')
    <a href="{{ route('prayers.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ ucfirst('prayer') }} List
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('prayers.update', $prayer->id) }}" enctype="multipart/form-data" method="post">
                @csrf
                @method('PATCH')

                <div class="row">
                    <div class="form-group">
                        <label class="col-sm-3 control-label my-2">{{ _lang('Date') }}</label>
                        <div class="col-sm-6">
                            <input type="date" class="form-control" name="date" value="{{ $prayer->date }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label my-2">{{ _lang('Subject') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="title" value="{{ $prayer->title }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label my-2">{{ _lang('Note') }}</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="summernote" name="note">{{ $prayer->note }}</textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label my-2">{{ _lang('File') }}</label>
                        <div class="col-sm-6">
                            <input type="file" class="form-control" name="file">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-4 col-sm-9">
                        <button type="submit" class="btn btn-primary mt-5">{{ _lang('Update') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
