@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Phone Book Category') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default" data-collapsed="0">
                <div class="card-header">
                    <div class="card-title pull-left">
                        {{ _lang('Update') }}
                    </div>

                    <div class="clearfix"></div>
                </div>

                <div class="">
                    <div class="col-lg-10">
                        <form action="{{ route('phone-book-category.update', $phoneBookCategory->id) }}" autocomplete="off"
                            enctype="multipart/form-data" method="post">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Category Title') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="name"
                                        value="{{ $phoneBookCategory->name }}" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Description') }}</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" name="description">{{ $phoneBookCategory->description }}</textarea>
                                </div>
                            </div>

                            <hr>
                            <div class="form-group">
                                <div class="col-sm-offset-3 col-sm-5">
                                    <button type="submit" class="btn btn-primary mt-5">Update</button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
