@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Subjects List') }}
@endsection

@section('create-button')
    <a href="{{ route('subjects.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add New Subject') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="row justify-content-center">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label class="control-label my-2">
                            <strong>{{ _lang('Select Class') }}</strong>
                        </label>
                        <select id="class" class="form-select form-select-sm form-select-solid" data-control="select2"
                            onchange="showClass(this);">

                            {!! create_option('classes', 'id', 'class_name', $class) !!}
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-responsive mt-5">
                <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                    <thead>
                        <th>{{ _lang('Subject Name') }}</th>
                        <th>{{ _lang('Subject Code') }}</th>
                        <th>{{ _lang('Class') }}</th>
                        <th>{{ _lang('Action') }}</th>
                    </thead>
                    <tbody>
                        @foreach ($subjects as $data)
                            <tr>
                                <td>{{ $data->subject_name }}</td>
                                <td>{{ $data->subject_code }}</td>
                                <td>{{ $data->class_name }}</td>

                                <td>
                                    <form action="{{ route('subjects.destroy', $data->id) }}" method="post">
                                        <a href="{{ route('subjects.edit', $data->id) }}" class="btn btn-warning btn-sm"><i
                                                class="fa fa-pencil" aria-hidden="true"></i></a>
                                        @method('DELETE')
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm show_confirm"><i
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
@endsection

@section('js-script')
    <script>
        function showClass(elem) {
            console.log('elem:', elem);
            if ($(elem).val() == "") {
                return;
            }
            window.location = "<?php echo url('subjects/class'); ?>/" + $(elem).val();
        }
    </script>
@stop
