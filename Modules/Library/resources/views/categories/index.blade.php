@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Book Categories') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card " data-collapsed="0">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Add New Category') }}
                    </div>
                </div>
                <div class="">
                    <form action="{{ route('book-categories.store') }}" enctype="multipart/form-data" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label class="control-label my-2">{{ _lang('Category Name') }}</label>
                                    <input type="text" class="form-control" name="category_name"
                                        value="{{ old('category_name') }}" placeholder="Category Name" required>

                                    @error('category_name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary mt-5">{{ _lang('Add Category') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card " data-collapsed="0">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Categories List') }}
                    </div>
                </div>
                <div class="table-responsive mt-5">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>#</th>
                            <th>{{ _lang('Name') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </thead>
                        <tbody>
                            @php
                                $i = 1;
                            @endphp
                            @foreach ($bookCategories as $data)
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>{{ $data->category_name }}</td>
                                    <td>
                                        <form action="{{ route('book-categories.destroy', $data->id) }}" method="post">
                                            <a href="{{ route('book-categories.edit', $data->id) }}"
                                                class="btn btn-warning btn-sm"><i class="fa fa-pencil"
                                                    aria-hidden="true"></i></a>
                                            @method('DELETE')
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm btn-remove"><i
                                                    class="fa fa-trash" aria-hidden="true"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @php
                                    $i++;
                                @endphp
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
