@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Dashboard') }}
@endsection
@section('create-button')
    <a href="{{ route('students.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add New Student') }}
    </a>
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
        <div class="col-lg-6">

            <form action="{{ route('students.bulk-file-upload.store') }}"enctype="multipart/form-data" method="post">
                @csrf

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="px-2 mb-2 control-label">{{ _lang('Academic Year') }}</label>
                            <select class="form-select form-select-sm form-select-solid" data-control="select2"
                                name="academic_year" required>
                                {!! create_option('academic_years', 'id', 'session', get_option('academic_year')) !!}
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="px-2 mb-2 control-label">{{ _lang('Class') }}</label>
                            <div class="">
                                <select name="class" class="form-select form-select-sm form-select-solid"
                                    data-control="select2" id="class" required>
                                    <option value="">{{ _lang('Select Class') }}</option>
                                    {!! create_option('classes', 'id', 'class_name', old('class')) !!}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="px-2 mb-2 control-label">{{ _lang('Section') }}</label>
                            <div class="">
                                <select name="section" class="form-select form-select-sm form-select-solid"
                                    data-control="select2" id="section" data-selected="{{ old('section') }}" required>
                                    <option value="">{{ _lang('Select Class First') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="px-2 mb-2 control-label">{{ _lang('Group') }}</label>
                            <div class="">
                                <select name="group" class="form-select form-select-sm form-select-solid"
                                    data-control="select2">

                                    {!! create_option('student_groups', 'id', 'group_name', old('group')) !!}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="px-2 mb-2 control-label">
                                {{ _lang('Excel File') }}
                            </label>

                            <div class="">
                                <input type="file" class="form-control" name="xlsx_file" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <button type="submit" class="pl-5 mt-5 btn btn-primary">{{ _lang('Submit') }}</button>
                        </div>
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
                    <a href="{{ route('students.download.demo.file') }}">
                        <i class="fa fa-file" class="icon_size"></i>

                        <span class="file_name">File Type: (.xlsx, .csv)</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
