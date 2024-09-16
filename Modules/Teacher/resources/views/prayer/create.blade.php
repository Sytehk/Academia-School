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
            <div class="card card-default" data-collapsed="0">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Application for prayer') }}
                    </div>
                </div>
                <div class="">
                    <div class="col-lg-10">
                        <form action="{{ route('prayers.store') }}" enctype="multipart/form-data" method="post">
                            @csrf

                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Date') }}</label>
                                <div class="col-sm-6">
                                    <input type="date" class="form-control" name="date" value="{{ old('date') }}"
                                        required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Subject') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="title" value="{{ old('title') }}"
                                        required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Note') }}</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" id="summernote" name="note">{{ old('note') }}</textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('File') }}</label>
                                <div class="col-sm-6">
                                    <input type="file" class="form-control" name="file">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-offset-3 col-sm-5">
                                    <button type="submit" class="btn btn-primary mt-5">Prayer</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
