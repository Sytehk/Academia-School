@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Assignment') }}
@endsection

@section('create-button')
    <a class="btn btn-sm fw-bold btn-primary" href="{{ route('assignments.index') }}">
        {{ _lang('Assignment List') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-default" data-collapsed="0">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Assignment') }}
                    </div>
                </div>
                <table class="table table-striped table-bordered" width="100%">
                    <tbody>
                        <tr>
                            <td>{{ _lang('Title') }}</td>
                            <td colspan="5"><b>{{ $assignment->title }}</b></td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Class') }}</td>
                            <td><b>{{ $assignment->class_name }}</b></td>
                            <td>{{ _lang('Section') }}</td>
                            <td><b>{{ $assignment->section_name }}</b></td>
                            <td>{{ _lang('Subject') }}</td>
                            <td><b>{{ $assignment->subject_name }}</b></td>
                        </tr>
                        <tr>
                            <td colspan="6">{!! $assignment->description !!}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Assignment Files') }}</td>
                            <td>
                                <li class="dropdown d-inline">
                                    <a href="#" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown">
                                        {{ _lang('Click to Download') }}
                                    </a>
                                    <ul class="dropdown-menu">
                                        @if ($assignment->file)
                                            <li><a
                                                    href="{{ asset('uploads/files/assignments/' . $assignment->file) }}">{{ $assignment->file }}</a>
                                            </li>
                                        @endif
                                        @if ($assignment->file_2)
                                            <li><a
                                                    href="{{ asset('uploads/files/assignments/' . $assignment->file_2) }}">{{ $assignment->file_2 }}</a>
                                            </li>
                                        @endif
                                        @if ($assignment->file_3)
                                            <li><a
                                                    href="{{ asset('uploads/files/assignments/' . $assignment->file_3) }}">{{ $assignment->file_3 }}</a>
                                            </li>
                                        @endif
                                        @if ($assignment->file_4)
                                            <li><a
                                                    href="{{ asset('uploads/files/assignments/' . $assignment->file_4) }}">{{ $assignment->file_4 }}</a>
                                            </li>
                                        @endif
                                    </ul>
                                </li>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
