@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Class') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="table-responsive border p-5">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Add New Class') }}
                    </div>
                </div>
                <div class="">
                    <form action="{{ route('class.store') }}" enctype="multipart/form-data" method="post">
                        @csrf
                        <div class="form-group">
                            <div class="col-lg-12">
                                <label class="control-label my-2">{{ _lang('Name') }}</label>
                                <input type="text" class="form-control" name="class_name" value="{{ old('class_name') }}"
                                    placeholder="Class Name" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-lg-12">
                                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Add Class') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="table-responsive border p-5">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Class List') }}
                    </div>
                </div>
                <div class="table-responsive mt-5">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>{{ _lang('Name') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($classes as $data)
                                <tr>
                                    <td>{{ $data->class_name }}</td>
                                    <td>
                                        <form action="{{ route('class.destroy', $data->id) }}" method="post">
                                            <a href="{{ route('class.edit', $data->id) }}"
                                                class="btn btn-warning btn-sm"><i class="fa fa-pencil"
                                                    aria-hidden="true"></i></a>
                                            @method('DELETE')
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm btn-remove"><i
                                                    class="fa fa-trash" aria-hidden="true"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
