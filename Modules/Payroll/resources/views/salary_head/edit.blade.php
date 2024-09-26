@extends('layouts.backend')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="panel-title">
                        {{ _lang('Update Salary Head') }}
                    </div>
                </div>
                <div class="panel-body">
                    <form action="{{ route('salary-heads.update', $salaryHead->id) }}" autocomplete="off"
                        class="form-horizontal form-groups-bordered validate" enctype="multipart/form-data" method="post"
                        accept-charset="utf-8">
                        @csrf
                        {{ method_field('PATCH') }}

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label">{{ _lang('Salary Head') }}</label>
                                        <input class="form-control" name="name" value="{{ $salaryHead->name }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label">{{ _lang('Nature') }}</label>
                                        <select class="form-control" name="type">
                                            <option value="">--Select One--</option>
                                            <option {{ $salaryHead->type == 'Addition' ? 'selected' : '' }}
                                                value="Addition">Addition</option>
                                            <option {{ $salaryHead->type == 'Deduction' ? 'selected' : '' }}
                                                value="Deduction">Deduction</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-info">{{ _lang('Update') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
