@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Phone Book') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default" data-collapsed="0">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Update Phone book') }}
                    </div>
                </div>

                <div class="">
                    <div class="col-lg-10">
                        <form action="{{ route('phone-book.update', $phoneBook->id) }}" autocomplete="off"
                            enctype="multipart/form-data" method="post">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Name') }}</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="name" value="{{ $phoneBook->name }}"
                                        required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Phone') }}</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="phone"
                                        value="{{ $phoneBook->phone }}" required>
                                </div>
                            </div>


                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Category') }}</label>
                                <div class="col-sm-9">
                                    <select name="category_id" class="form-select form-select-sm form-select-solid"
                                        data-control="select2" id="" required>
                                        <option value="">{{ _lang('--Select--') }}</option>
                                        @foreach ($phoneBookCategories as $category)
                                            <option value="{{ (int) $category->id }}"
                                                {{ $phoneBook->category_id == $category->id ? 'selected' : '' }}>
                                                {{ _lang($category->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label my-2">{{ _lang('Note') }}</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" name="note">{{ $phoneBook->note }}</textarea>
                                </div>
                            </div>


                            <div class="form-group">
                                <div class="col-sm-offset-3 col-sm-5">
                                    <button type="submit" class="btn btn-primary mt-5">Update</button>
                                </div>
                            </div>
                            <hr>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
