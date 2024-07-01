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
                        {{ _lang('Update Class') }}
                    </div>
                </div>
                <div class="">
                    <form action="{{ route('class.update', $class->id) }}" method="post">
                        @csrf
                        @method('PATCH')
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Name') }}</label>
                                <input type="text" class="form-control" name="class_name"
                                    value="{{ $class->class_name }}" placeholder="Class Name" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Update Class') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
