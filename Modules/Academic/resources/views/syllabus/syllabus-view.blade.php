@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Syllabus') }}
@endsection

@section('create-button')
    <a class="btn btn-sm fw-bold btn-primary" href="{{ route('syllabus.index') }}">
        {{ _lang('Syllabus List') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card shadow-sm p-5">
                <div class="card-header">
                    <h5 class="mb-0">{{ _lang('Syllabus Details') }}</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <td class="fw-bold px-5">{{ _lang('Title') }}</td>
                                <td>{{ $syllabus->title }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold px-5">{{ _lang('Description') }}</td>
                                <td>{!! $syllabus->description !!}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold px-5">{{ _lang('Class') }}</td>
                                <td>{{ $syllabus->class_name }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold px-5">{{ _lang('File') }}</td>
                                <td>
                                    <a class="btn btn-primary btn-sm"
                                        href="{{ asset('uploads/files/syllabus/' . $syllabus->file) }}">
                                        {{ _lang('Click to Download') }}
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
