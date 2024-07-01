@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Deparment') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="table-responsive border p-5">
                <div class="card-header">
                    <span class="card-title">
                        {{ _lang('Add New Department') }}
                    </span>
                </div>
                <div class="">

                    <form action="{{ route('departments.store') }}"enctype="multipart/form-data" method="post">
                        @csrf

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Department Name') }}</label>
                                <input type="text" class="form-control" name="department_name" required
                                    value="{{ old('department_name') }}" placeholder="Department Name">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Priority') }}</label>
                                <input type="number" class="form-control" name="priority" min="1" required
                                    value="{{ old('priority') }}" placeholder="Priority">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-5">
                                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Add Department') }}</button>
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
                        {{ _lang('Department List') }}
                    </span>
                </div>
                <div class="table-responsive mt-5">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>{{ _lang('Department Name') }}</th>
                            <th>{{ _lang('Priority') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($departments as $data)
                                <tr>

                                    <td>{{ $data->department_name }}</td>
                                    <td>{{ $data->priority }}</td>

                                    <td>
                                        <form action="{{ route('departments.destroy', $data->id) }}" method="post">
                                            <a href="{{ route('departments.edit', $data->id) }}"
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
            window.location = "<?php echo url('departments/class'); ?>/" + $(elem).val();
        }
    </script>
@stop
