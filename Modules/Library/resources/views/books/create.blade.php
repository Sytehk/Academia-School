@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Books') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default" data-collapsed="0">
                <div class="card-header">

                    <span class="card-title">{{ _lang('Add New Book') }}</span>

                    <div class="pull-right" style="text-align: right;">
                        <a href="{{ route('books.bulk-upload') }}"
                            class="btn btn-primary btn-sm">{{ _lang('Bulk Upload') }}</a>
                    </div>
                </div>
                <div class="">
                    <div class="col-lg-8">
                        <form action="{{ route('books.store') }}" enctype="multipart/form-data" method="post">
                            @csrf
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Name') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="book_name"
                                        value="{{ old('book_name') }}" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Code') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="code" value="{{ old('code') }}"
                                        required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Category') }}</label>
                                <div class="col-sm-9">
                                    <select name="category" class="form-select form-select-sm form-select-solid"
                                        data-control="select2" id="class" required>

                                        {!! create_option('book_categories', 'id', 'category_name', old('category')) !!}
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Author') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="author" value="{{ old('author') }}"
                                        required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Publisher') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="publisher"
                                        value="{{ old('publisher') }}" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Rack No') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="rack" value="{{ old('rack') }}"
                                        required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Quantity') }}</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="quantity"
                                        value="{{ old('quantity') }}" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Description') }}</label>
                                <div class="col-sm-9">
                                    <textarea name="description" class="form-control">{{ old('description') }}</textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Publish Year') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="publish_year"
                                        value="{{ old('publish_year') }}" required>
                                </div>
                            </div>
                            {{-- <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Photo') }}</label>
                                <div class="col-sm-9">
                                    <input type="file" class="form-control dropify" name="photo"
                                        data-allowed-file-extensions="png jpg jpeg PNG JPG JPEG">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Barcode') }}</label>
                                <div class="col-sm-9">
                                    <input type="file" class="form-control dropify" name="barcode"
                                        data-allowed-file-extensions="png jpg jpeg PNG JPG JPEG">
                                </div>
                            </div> --}}

                            <div class="form-group">
                                <div class="col-sm-offset-4 col-sm-9">
                                    <button type="submit" class="btn btn-primary mt-5">{{ _lang('Add Book') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
