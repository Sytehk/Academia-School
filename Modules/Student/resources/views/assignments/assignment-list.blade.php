@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Dashboard') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="header">
                    <div class="col-lg-6">
                        <h4 class="title">{{ _lang('My Assignments') }}</h4>
                    </div>
                </div>
                <div class="content no-export">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>{{ _lang('Title') }}</th>
                            <th>{{ _lang('Description') }}</th>
                            <th>{{ _lang('Subject') }}</th>
                            <th>{{ _lang('Details') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($assignments as $data)
                                <tr>
                                    <td>{{ $data->title }}</td>
                                    <td>{{ substr(strip_tags($data->description), 0, 100) }}...</td>
                                    <td>{{ $data->subject_name }}</td>
                                    <td>
                                        <a href="{{ url('student/view-assignment/' . $data->id) }}"
                                            class="btn btn-primary btn-sm ajax-modal"
                                            data-title="{{ _lang('View Assignment') }}" data-fullscreen="true">
                                            <i class="fa fa-eye" aria-hidden="true"></i>
                                            Submit Assignment
                                        </a>
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
