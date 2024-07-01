@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Signature') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Update Signature') }}
                    </div>
                </div>
                <div class="">
                    <form action="{{ route('signatures.update', $signature->id) }}" autocomplete="off"
                        enctype="multipart/form-data" method="post">
                        @csrf
                        @method('PATCH')

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Place At') }}</label>
                                <input type="text" class="form-control" name="place_at" required
                                    value="{{ $signature->place_at }}" placeholder="Place At">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Title') }}</label>
                                <input type="text" class="form-control" name="title" required
                                    value="{{ $signature->title }}" placeholder="Title">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Signature') }}</label>
                                <input type="file" class="form-control dropify" name="image"
                                    data-default-file="{{ asset('uploads/' . $signature->image) }}"
                                    data-allowed-file-extensions="png jpg jpeg PNG JPG JPEG">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Update Signature') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
