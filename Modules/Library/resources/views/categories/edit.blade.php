@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Book Category') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="table-responsive border p-5">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Edit Category') }}
                    </div>
                </div>
                <div class="">
                    <form action="{{ route('book-categories.update', $bookCategory->id) }}" enctype="multipart/form-data"
                        method="post">
                        @csrf
                        @method('PATCH')
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Category Name') }}</label>
                                <input type="text" class="form-control" name="category_name"
                                    value="{{ $bookCategory->category_name }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Update Category') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
