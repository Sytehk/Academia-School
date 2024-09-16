@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Dashboard') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-default" data-collapsed="0">
                <div class="card-header">
                    <span class="card-title">{{ _lang('My Profile') }}</span>
                    <div class="pull-right">
                        <a class="btn btn-primary btn-sm" href="{{ url('profile/edit') }}">
                            <i class="fa fa-file"></i>
                            {{ _lang('Profile update') }}
                        </a>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <table class="table table-striped table-bordered" width="100%">
                    <tbody>
                        <tr>
                            <td style="text-align: center;" colspan="4"><img width="200px" style="border-radius: 7px;"
                                    src="{{ asset('uploads/images/' . $teacher->image) }}"></td>
                        </tr>
                        <tr>
                            <td colspan="2">{{ _lang('Name') }}</td>
                            <td colspan="2">{{ $teacher->name }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Designation') }}</td>
                            <td>{{ $teacher->designation }}</td>
                            <td>{{ _lang('Date Of Birth') }}</td>
                            <td>{{ $teacher->birthday }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Gender') }}</td>
                            <td>{{ $teacher->gender }}</td>
                            <td>{{ _lang('Religion') }}</td>
                            <td>{{ $teacher->religion }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Joining Date') }}</td>
                            <td>{{ $teacher->joining_date }}</td>
                            <td>{{ _lang('Address') }}</td>
                            <td>{{ $teacher->address }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Phone') }}</td>
                            <td>{{ $teacher->phone }}</td>
                            <td>{{ _lang('Email') }}</td>
                            <td>{{ $teacher->email }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
