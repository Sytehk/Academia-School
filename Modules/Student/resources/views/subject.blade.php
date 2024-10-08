@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Dashboard') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('My Subjects') }}
                    </div>
                </div>
                <div class="table-responsive mt-5">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>{{ _lang('SL No.') }}</th>
                            <th>{{ _lang('Subject Name') }}</th>
                            <th>{{ _lang('Subject Code') }}</th>
                            <th>{{ _lang('Class') }}</th>

                        </thead>
                        <tbody>
                            @foreach ($subjects as $data)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td>{{ $data->subject_name }}</td>
                                    <td>{{ $data->subject_code }}</td>
                                    <td>{{ $data->class_name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endsection
