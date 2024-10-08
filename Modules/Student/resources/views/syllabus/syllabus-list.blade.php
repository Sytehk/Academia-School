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
                        <h4 class="title">{{ _lang('My Syllabus') }}</h4>
                    </div>
                </div>
                <div class="content no-export">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>{{ _lang('Title') }}</th>
                            <th>{{ _lang('Description') }}</th>
                            <th>{{ _lang('Class') }}</th>
                            <th>{{ _lang('File') }}</th>
                            <th style="width:120px">{{ _lang('View Details') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($syllabus as $data)
                                <tr>
                                    <td>{{ $data->title }}</td>
                                    <td>{{ substr(strip_tags($data->description), 0, 100) }}...</td>
                                    <td>{{ $data->class_name }}</td>
                                    <td>{{ $data->file }}</td>
                                    <td>
                                        <a href="{{ asset('uploads/files/syllabus/' . $data->file) }}" target="_blank"
                                            class="btn btn-primary btn-sm rect-btn"><i class="fa fa-download"
                                                aria-hidden="true"></i></a>
                                        <a href="{{ url('student/view-syllabus/' . $data->id) }}"
                                            class="btn btn-primary btn-sm ajax-modal rect-btn"
                                            data-title="{{ _lang('View Syllabus') }}" data-fullscreen="true"><i
                                                class="fa fa-eye" aria-hidden="true"></i></a>
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
