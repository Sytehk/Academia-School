@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Teacher') }}
@endsection
<style>
    img {
        border-radius: 7px;
    }
</style>
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default" data-collapsed="0">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fa fa-dot-circle-o"></i>{{ _lang('Teacher Profile') }}
                    </div>
                </div>
                <table class="table table-striped table-bordered" width="100%">
                    <tbody>
                        <tr>
                            <td class="text-center" colspan="4">
                                @if ($teacher->image != null)
                                    <img src="{{ $teacher->image ? asset('storage/users/' . $teacher->image) : asset('assets/media/svg/files/blank-image.svg') }}"
                                        class="me-3" alt="" width="50px">
                                @else
                                    <img src="{{ asset('uploads/images/' . $teacher->image) }}" width="50px"
                                        alt="">
                                @endif
                            </td>
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
                            <td>Address</td>
                            <td>{{ $teacher->address }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Phone') }}</td>
                            <td>{{ $teacher->phone }}</td>
                            <td>{{ _lang('Email') }}</td>
                            <td>{{ $teacher->email }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Blood Group') }}</td>
                            <td>{{ $teacher->blood }}</td>
                            <td>{{ _lang('SL') }}</td>
                            <td>{{ $teacher->sl }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
