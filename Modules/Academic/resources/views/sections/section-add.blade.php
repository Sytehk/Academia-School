@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Section') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="table-responsive border p-5">
                <div class="card-header">
                    <span class="card-title">
                        {{ _lang('Add New Section') }}
                    </span>
                </div>
                <div class="">
                    <form action="{{ route('sections.store') }}"enctype="multipart/form-data" method="post">
                        @csrf

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Class(es)') }}</label>
                                <select name="class_id[]" class="form-select form-select-sm form-select-solid"
                                    data-control="select2" data-close-on-select="false" multiple required>

                                    {!! create_option('classes', 'id', 'class_name') !!}
                                </select>
                                <small class="text-muted">{{ _lang('Select one or more classes — a section row is created for each.') }}</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Section Name') }}</label>
                                <input type="text" class="form-control" name="section_name" required
                                    value="{{ old('section_name') }}" placeholder="Section Name">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">Group Name</label>
                                <select name="student_group_id" class="form-control select2">
                                    <option value="">Select Group Name</option>
                                    {!! create_option('student_groups', 'id', 'group_name', old('student_group_id')) !!}
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Room No/Name') }}</label>
                                <input type="text" class="form-control" name="room_no" value="{{ old('room_no') }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-5">
                                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Add Section') }}</button>
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
                        {{ _lang('Section List') }}
                    </span>

                    <select id="class" class="select_class pull-right" onchange="showClass(this);">
                        <option value="">{{ _lang('Select Class') }}</option>
                        {!! create_option('classes', 'id', 'class_name', $class) !!}
                    </select>
                </div>
                <div class="table-responsive mt-5">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>{{ _lang('Class Name') }}</th>
                            <th>{{ _lang('Section Name') }}</th>
                            <th>{{ _lang('Group') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($sections as $data)
                                <tr>
                                    <td>{{ $data->class?->class_name }}</td>
                                    <td>{{ $data->section_name }}</td>
                                    <td>{{ $data->group_name }}</td>
                                    <td>
                                        <form action="{{ route('sections.destroy', $data->id) }}" method="post">
                                            @csrf
                                            @method('DELETE')

                                            <a href="{{ route('sections.edit', $data->id) }}"
                                                class="btn btn-warning btn-sm"><i class="fa fa-pencil"
                                                    aria-hidden="true"></i></a>
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
            window.location = "<?php echo url('sections-class'); ?>/" + $(elem).val();
        }
    </script>
@stop
