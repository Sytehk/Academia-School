@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Remark Config') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="table-responsive border p-5">
                <div class="card-header">
                    <span class="card-title">
                        {{ _lang('Add New Remarks') }}
                    </span>
                </div>
                <div class="">
                    <form action="{{ route('remarks-config.store') }}"enctype="multipart/form-data" method="post">
                        @csrf

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Remark Title') }}</label>
                                <input type="text" class="form-control" name="remark_title" placeholder="Remark Title"
                                    required value="{{ old('remark_title') }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Remarks') }}</label>
                                <textarea type="text" class="form-control" name="remarks" placeholder="Remarks" cols="4" rows="4">{{ old('remarks') }}</textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-5">
                                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Create Remarks') }}</button>
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
                        {{ _lang('Remarks Config List') }}
                    </span>
                </div>
                <div class="table-responsive mt-5">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>{{ _lang('SL NO.') }}</th>
                            <th>{{ _lang('Title') }}</th>
                            <th>{{ _lang('Remarks') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($remarks_configs as $remarks_config)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td>{{ $remarks_config->remark_title }}</td>
                                    <td>{{ $remarks_config->remarks }}</td>
                                    <td>
                                        <form action="{{ route('remarks-config.destroy', $remarks_config->id) }}"
                                            method="post">
                                            <a href="{{ route('remarks-config.edit', $remarks_config->id) }}"
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
