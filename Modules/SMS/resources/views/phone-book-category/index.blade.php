@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Phone Book Category') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('New Category') }}
                    </div>
                </div>

                <div class="">
                    <form action="{{ route('phone-book-category.store') }}"enctype="multipart/form-data" method="post">
                        @csrf
                        <div class="form-group">
                            <label class="col-sm-3 control-label my-2">{{ _lang('Category Title') }}</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="name" value="{{ old('name') }}"
                                    placeholder="Category title" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label my-2">{{ _lang('Description') }}</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" name="description" placeholder="Description">{{ old('description') }}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-5">
                                <button type="submit" class="btn btn-primary mt-5">Create</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Phone Book Categories') }}
                    </div>
                </div>

                <div class="">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>{{ _lang('Category Title') }}</th>
                            <th>{{ _lang('Description') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </thead>
                        <tbody>
                            @if (isset($phoneBookCategories))
                                @foreach ($phoneBookCategories as $category)
                                    <tr>

                                        <td>{{ $category->name }}</td>
                                        <td>{{ $category->description }}</td>
                                        <td>
                                            <form action="{{ route('phone-book-category.destroy', $category->id) }}"
                                                method="post" class="d-flex">
                                                {{-- <a href="{{ route('phone-book-categoryshow', $category->id) }}"
                                                    class="btn btn-primary btn-sm"><i class="fa fa-eye"
                                                        aria-hidden="true"></i></a> --}}
                                                <a href="{{ route('phone-book-category.edit', $category->id) }}"
                                                    class="btn btn-warning btn-sm mx-2"><i class="fa fa-pencil"
                                                        aria-hidden="true"></i></a>
                                                @method('DELETE')
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm btn-remove"><i
                                                        class="fa fa-trash" aria-hidden="true"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7" class="text-danger">Not Found</td>
                                </tr>
                            @endif

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
