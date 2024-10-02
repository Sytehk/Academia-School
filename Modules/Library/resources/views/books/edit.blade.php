@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Book') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default" data-collapsed="0">
                <div class="card-header">
                    <div class="card-title">
                        <i class="entypo-plus-circled"></i>{{ _lang('Edit Book') }}
                    </div>
                </div>
                <div class="">
                    <div class="col-lg-8">
                        <form action="{{ route('books.update', $book->id) }}" enctype="multipart/form-data" method="post">
                            @csrf
                            @method('PATCH')
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Name') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="book_name"
                                        value="{{ $book->book_name }}" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Code') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="code" value="{{ $book->code }}"
                                        required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Author') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="author" value="{{ $book->author }}"
                                        required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Publisher') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="publisher"
                                        value="{{ $book->publisher }}" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Rack No') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="rack" value="{{ $book->rack }}"
                                        required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Quantity') }}</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="quantity"
                                        value="{{ $book->quantity }}" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Description') }}</label>
                                <div class="col-sm-9">
                                    <textarea name="description" class="form-control">{{ $book->description }}</textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Publish Year') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="publish_year"
                                        value="{{ $book->publish_year }}" required>
                                </div>
                            </div>
                            {{-- <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Photo') }}</label>
                                <div class="col-sm-9">
                                    <input type="file" class="form-control dropify"
                                        data-default-file="{{ asset('uploads/images/books/' . $book->photo) }}"
                                        name="photo" data-allowed-file-extensions="png jpg jpeg PNG JPG JPEG">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Barcode') }}</label>
                                <div class="col-sm-9">
                                    <input type="file" class="form-control dropify"
                                        data-default-file="{{ asset('uploads/images/books/bar_codes' . $book->barcode) }}"
                                        name="barcode" data-allowed-file-extensions="png jpg jpeg PNG JPG JPEG">
                                </div>
                            </div> --}}

                            <div class="form-group">
                                <div class="col-sm-offset-4 col-sm-9">
                                    <button type="submit" class="btn btn-primary mt-5">{{ _lang('Update Book') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
