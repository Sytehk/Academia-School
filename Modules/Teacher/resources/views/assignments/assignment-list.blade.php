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
                        <h4 class="title">{{ _lang('Assignments List') }}</h4>
                    </div>
                    <div class="col-lg-6" style="text-align: right;">
                        <a href="{{ url('teacher/create-assignment') }}"
                            class="btn btn-primary btn-sm">{{ _lang('Add New Assignment') }}</a>
                    </div>
                </div>
                <div class="content no-export">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>{{ _lang('Title') }}</th>
                            <th>{{ _lang('Description') }}</th>
                            <th>{{ _lang('Class') }}</th>
                            <th>{{ _lang('Section') }}</th>
                            <th>{{ _lang('Subject') }}</th>
                            <th style="width: 155px;">{{ _lang('Action') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($assignments as $data)
                                <tr>
                                    <td>{{ $data->title }}</td>
                                    <td>{{ substr(strip_tags($data->description), 0, 100) }}...</td>
                                    <td>{{ $data->class_name }}</td>
                                    <td>{{ $data->section_name }}</td>
                                    <td>{{ $data->subject_name }}</td>
                                    <td>
                                        @if ($data->file)
                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-primary btn-sm dropdown-toggle" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fa fa-download"></i>
                                                </button>

                                                <ul class="dropdown-menu">
                                                    @if ($data->file)
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ asset('uploads/files/assignments/' . $data->file) }}">
                                                                {{ _lang('File 1') }}
                                                            </a>
                                                        </li>
                                                    @endif

                                                    @if ($data->file_2)
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ asset('uploads/files/assignments/' . $data->file_2) }}">
                                                                {{ _lang('File 2') }}
                                                            </a>
                                                        </li>
                                                    @endif

                                                    @if ($data->file_3)
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ asset('uploads/files/assignments/' . $data->file_3) }}">
                                                                {{ _lang('File 3') }}
                                                            </a>
                                                        </li>
                                                    @endif

                                                    @if ($data->file_4)
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ asset('uploads/files/assignments/' . $data->file_4) }}">
                                                                {{ _lang('File 4') }}
                                                            </a>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        @endif
                                        <a href="{{ url('teacher/assignment/' . $data->id) }}"
                                            class="btn btn-primary btn-sm"><i class="fa fa-eye" aria-hidden="true"></i></a>
                                        <a href="{{ url('teacher/edit-assignment/' . $data->id) }}"
                                            class="btn btn-warning btn-sm"><i class="fa fa-pencil"
                                                aria-hidden="true"></i></a>
                                        <a href="{{ url('teacher/destroy_assignment/' . $data->id) }}"
                                            class="btn btn-danger btn-sm btn-remove"><i class="fa fa-trash"
                                                aria-hidden="true"></i></a>
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
