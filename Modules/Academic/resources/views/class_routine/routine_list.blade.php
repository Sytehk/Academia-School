@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Class Routine') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default">
                <div class="card-header">
                    <span class="card-title">{{ _lang('Routine List') }}</span>

                    <select id="class" class="select_class pull-right" onchange="showClass(this);">

                        {!! create_option('classes', 'id', 'class_name', $class) !!}
                    </select>
                </div>

                <div class="table-responsive mt-5">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>{{ _lang('Class') }}</th>
                            <th>{{ _lang('Section') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($routine_list as $data)
                                <tr>
                                    <td>{{ $data->class_name }}</td>
                                    <td>{{ $data->section_name }}</td>
                                    <td>
                                        <a href="{{ url('class-routines-manage/' . $data->c_id . '/' . $data->s_id) }}"
                                            class="btn btn-primary btn-sm"><i class="fa fa-pencil"></i> Manage Routine</a>
                                        <a href="{{ url('class-routines-show/' . $data->c_id . '/' . $data->s_id) }}"
                                            class="btn btn-primary btn-sm"><i class="fa fa-search"></i> View Routine</a>
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
            window.location = "<?php echo url('class_routines-class/'); ?>/" + $(elem).val();
        }
    </script>
@stop
