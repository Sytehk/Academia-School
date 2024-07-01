@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Class Assign') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="table-responsive border p-5">
                <div class="card-header">
                    <span class="card-title">
                        {{ _lang('Edit Class Assign') }}
                    </span>
                </div>
                <div class="">
                    <form action="{{ route('assign-class.update', $assign_class->id) }}" autocomplete="off"
                        enctype="multipart/form-data" method="post">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Teacher') }}</label>
                                <select name="teacher_id" class="form-select form-select-sm form-select-solid"
                                    data-control="select2" required>

                                    {!! create_option('teachers', 'id', 'name', $assign_class->teacher_id) !!}
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Class') }}</label>
                                <select name="class_id" class="form-select form-select-sm form-select-solid"
                                    data-control="select2" required>

                                    {!! create_option('classes', 'id', 'class_name', $assign_class->class_id) !!}
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-5">
                                <button type="submit"
                                    class="btn btn-primary mt-5">{{ _lang('Edit Class Assign') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
