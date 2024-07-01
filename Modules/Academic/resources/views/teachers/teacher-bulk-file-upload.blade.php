@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Teachers Bulk Upload') }}
@endsection

@section('create-button')
    <a href="{{ route('teachers.create') }}" class="btn btn-sm fw-bold btn-primary">{{ _lang('Add New Teacher') }}</a>
@endsection

@section('content')
    @if (session('bulk_import_summary'))
        @php $summary = session('bulk_import_summary'); @endphp
        <div class="row mb-5">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ _lang('Import Summary') }}</h4>
                    </div>
                    <div class="card-body">
                        <span class="badge bg-success me-2">{{ _lang('Imported') }}: {{ $summary['imported'] }}</span>
                        <span class="badge bg-warning me-2">{{ _lang('Skipped') }}: {{ $summary['skipped'] }}</span>
                        <span class="badge bg-danger me-2">{{ _lang('Failed') }}: {{ $summary['failed'] }}</span>

                        @if (!empty($summary['details']))
                            <div class="table-responsive mt-4">
                                <table class="table table-sm table-row-dashed">
                                    <thead>
                                        <tr>
                                            <th>{{ _lang('Row') }}</th>
                                            <th>{{ _lang('Status') }}</th>
                                            <th>{{ _lang('Reason') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($summary['details'] as $detail)
                                            @if ($detail['status'] !== 'imported')
                                                <tr>
                                                    <td>{{ $detail['row'] }}</td>
                                                    <td>
                                                        <span
                                                            class="badge {{ $detail['status'] === 'skipped' ? 'bg-warning' : 'bg-danger' }}">
                                                            {{ ucfirst($detail['status']) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $detail['reason'] }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default" data-collapsed="0">
                <div class="row">
                    <div class="col-lg-6">
                        <form action="{{ route('teachers.bulk-file-upload.store') }}" autocomplete="off"
                            enctype="multipart/form-data" method="post">
                            @csrf

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="px-5 form-group">
                                        <label class="my-2 control-label">{{ _lang('Designation') }}</label>
                                        <select class="form-select form-select-sm form-select-solid" data-control="select2"
                                            name="designation" required>
                                            <option value="designation">{{ _lang('Select Designation') }}</option>
                                            {!! create_option('picklists', 'value', 'value', old('designation'), ['type' => 'Designation']) !!}
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="px-5 form-group">
                                        <label class="my-2 control-label">{{ _lang('Department') }}</label>
                                        <select class="form-select form-select-sm form-select-solid" data-control="select2"
                                            name="department_id" required>
                                            <option value="">{{ _lang('Select Department') }}</option>
                                            @foreach ($departments as $department)
                                                <option value="{{ $department->id }}">
                                                    {{ $department->department_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="px-5 form-group">
                                        <label class="mb-2 control-label">
                                            {{ _lang('Excel File') }}
                                        </label>

                                        <div class="">
                                            <input type="file" class="form-control" name="xlsx_file" required>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="form-group">
                                <div class="text-right">
                                    <button type="submit" class="mt-5 btn btn-primary">{{ _lang('Submit') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-lg-6">
                        <div class="text-center card card-default">
                            <div class="card-header">
                                <h4>Sample Excel Format</h4>
                            </div>
                            <div class="card-body">
                                <a href="{{ route('teachers.download.demo.file') }}">
                                    <i class="fa fa-file icon_size"></i>
                                    <span class="span_m_p">File Type: (.xlsx, .csv)</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js-script')
    <script>
        $(window).on('load', function() {
            $("#section").next().find("ul li").css("display", "none");
            $(document).on("change", "#class", function() {
                $("#section").next().find("ul li").css("display", "none");
                var class_id = $(this).val();
                $('#section option[data-class="' + class_id + '"]').each(function() {
                    var section_id = $(this).val();
                    $("#section").next().find("ul li[data-value='" + section_id + "']").css(
                        "display", "block");
                });
            });
        });
    </script>
@stop
