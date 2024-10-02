@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Book Issue Edit') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default" data-collapsed="0">
                <div class="card-header">
                    <div class="card-title">
                        <i class="entypo-plus-circled"></i>{{ _lang('Edit Issue') }}
                    </div>
                </div>
                <div class="">
                    <div class="col-lg-6">
                        <form action="{{ route('bookissues.update', $issue->id) }}" enctype="multipart/form-data"
                            method="post">
                            @csrf
                            @method('PATCH')
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Library Id') }}</label>
                                <div class="col-sm-9">
                                    <select name="library_id" class="form-select form-select-sm form-select-solid"
                                        data-control="select2" required>

                                        {!! create_option('library_members', 'library_id', 'library_id', $issue->library_id) !!}
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Book') }}</label>
                                <div class="col-sm-9">
                                    <select name="book_id" class="form-select form-select-sm form-select-solid"
                                        data-control="select2" required>

                                        {!! create_option('books', 'id', 'book_name', $issue->book_id) !!}
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Due Date') }}</label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control" name="due_date"
                                        value="{{ $issue->due_date }}" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Note') }}</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" name="note">{{ $issue->note }}</textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-3 col-sm-5">
                                    <button type="submit" class="btn btn-primary mt-5">{{ _lang('Update Issue') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
