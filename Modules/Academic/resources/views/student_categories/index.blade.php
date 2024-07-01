@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Student Category') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="table-responsive border p-5">
                <div class="card-header">
                    <span class="card-title">
                        {{ _lang('Add New Student Category') }}
                    </span>
                </div>
                <div class="">
                    <form action="{{ route('student-categories.store') }}"enctype="multipart/form-data" method="post">
                        @csrf

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Student Category') }}</label>
                                <input type="text" class="form-control" name="name" required
                                    value="{{ old('name') }}" placeholder="Student Category">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <button type="submit"
                                    class="btn btn-primary mt-5">{{ _lang('Add Student Category') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="table-responsive border p-5">
                <div class="card-header">
                    <span class="card-title">
                        {{ _lang('Student Category List') }}
                    </span>
                </div>
                <div class="table-responsive mt-5">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>{{ _lang('Student Category') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($student_categories as $data)
                                <tr>
                                    <td>{{ $data->name }}</td>
                                    <td>
                                        <form action="{{ route('student-categories.destroy', $data->id) }}" method="post">
                                            <a href="{{ route('student-categories.edit', $data->id) }}"
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

@section('js-script')
    <script>
        function showClass(elem) {
            if ($(elem).val() == "") {
                return;
            }
            window.location = "<?php echo url('student-categories/class'); ?>/" + $(elem).val();
        }
    </script>
@stop
