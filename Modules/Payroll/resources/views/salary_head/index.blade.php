@extends('layouts.backend')
@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default" data-collapsed="0">
                <div class="panel-heading">
                    <span class="panel-title">
                        {{ _lang('Add Salary Head') }}
                    </span>
                </div>
                <div class="panel-body">
                    <form action="{{ route('salary-heads.store') }}" autocomplete="off"
                        class="form-horizontal form-groups-bordered validate" method="post" accept-charset="utf-8">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label">{{ _lang('Salary Head') }}</label>
                                        <input class="form-control" name="name" value="{{ old('name') }}"
                                            placeholder="Salary Head" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label">{{ _lang('Nature') }}</label>
                                        <select class="form-control" name="type" required>
                                            <option value="">--Select One--</option>
                                            <option value="Addition">Addition</option>
                                            <option value="Deduction">Deduction</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-5">
                                <button type="submit" class="btn btn-info">{{ _lang('Save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="panel panel-default" data-collapsed="0">
                <div class="panel-heading">
                    <span class="panel-title">
                        {{ _lang('Salary Head List') }}
                    </span>
                </div>
                <div class="panel-body no-export">
                    <table class="table table-bordered data-table">
                        <thead>
                            <th>{{ _lang('SL No.') }}</th>
                            <th>{{ _lang('Salary Head') }}</th>
                            <th>{{ _lang('Nature') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($salaryHeads as $salaryHead)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>

                                    <td>{{ $salaryHead->name }}</td>
                                    <td>{{ $salaryHead->type }}</td>

                                    <td>
                                        <form action="{{ route('salary-heads.destroy', $salaryHead->id) }}" method="post">
                                            <a href="{{ route('salary-heads.edit', $salaryHead->id) }}"
                                                class="btn btn-warning btn-xs"><i class="fa fa-pencil"
                                                    aria-hidden="true"></i></a>
                                            {{ method_field('DELETE') }}
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-xs btn-remove"><i
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
