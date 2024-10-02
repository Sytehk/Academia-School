@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Books Bulk Upload') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default" data-collapsed="0">
                <div class="card-header">
                    <div class="card-title pull-left">
                        {{ _lang('Books Bulk Upload') }}
                    </div>
                    <div class="pull-right">
                        <a class="btn btn-primary btn-sm" href="{{ route('books.create') }}">
                            <i class="fa fa-upload"></i>
                            {{ _lang('Book Create') }}
                        </a>
                    </div>
                    <div class="clearfix"></div>
                </div>

                <div class="">
                    <div class="col-lg-6">
                        <form action="{{ route('books.bulk-upload.store') }}" autocomplete="off"
                            enctype="multipart/form-data" method="post">
                            @csrf

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group" style="padding: 0 15px;">
                                        <label class="mb-2 control-label" style="margin: 5px 0;">
                                            {{ _lang('Excel File') }}
                                        </label>

                                        <div class="">
                                            <input type="file" class="form-control" name="xlsx_file" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <button type="submit" class="ml-5 btn btn-primary">{{ _lang('Submit') }}</button>
                                    </div>
                                </div>
                            </div>


                        </form>
                    </div>

                    <div class="col-lg-6">
                        <div class="text-center card card-default">
                            <div class="card-header">
                                <h4>Sample Excel Format</h4>
                            </div>
                            <div class="card-body">
                                <a href="{{ route('books.bulk-upload-demo.download') }}">
                                    <i class="fa fa-file icon_size"></i>
                                    <span class="span_m_p">File Type: (.xlsx, .csv)</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
