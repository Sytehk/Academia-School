@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Deparment') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Update Department') }}
                    </div>
                </div>
                <div class="">
                    <form action="{{ route('departments.update', $department->id) }}" autocomplete="off"
                        enctype="multipart/form-data" method="post">
                        @csrf
                        @method('PATCH')
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Department Name') }}</label>
                                <input type="text" class="form-control" name="department_name"
                                    value="{{ $department->department_name }}" placeholder="Department Name" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Priority') }}</label>
                                <input type="number" class="form-control" min="1" name="priority" required
                                    value="{{ $department->priority }}" placeholder="Priority">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <button type="submit"
                                    class="btn btn-primary mt-5">{{ _lang('Update Department') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
