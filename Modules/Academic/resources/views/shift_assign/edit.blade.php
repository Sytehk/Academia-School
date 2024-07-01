@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Shift Assign') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="table-responsive border p-5">
                <div class="card-header">
                    <span class="card-title">
                        {{ _lang('Edit Shift Assign') }}
                    </span>
                </div>
                <div class="">
                    <form action="{{ route('assign-shifts.update', $assign_shift->id) }}" autocomplete="off"
                        enctype="multipart/form-data" method="post">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Teacher') }}</label>
                                <select name="teacher_id" class="form-select form-select-sm form-select-solid"
                                    data-control="select2" required>

                                    {!! create_option('teachers', 'id', 'name', $assign_shift->teacher_id) !!}
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Shift') }}</label>
                                <select name="shift_id" class="form-select form-select-sm form-select-solid"
                                    data-control="select2" required>

                                    {!! create_option('shifts', 'id', 'name', $assign_shift->shift_id) !!}
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-5">
                                <button type="submit"
                                    class="btn btn-primary mt-5">{{ _lang('Edit Shift Assign') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
