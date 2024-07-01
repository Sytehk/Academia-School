@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Staff View') }}
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
                        <i class="fa fa-dot-circle-o"></i>{{ _lang('Staff Profile') }}
                    </div>
                </div>
                <table class="table table-striped table-bordered" width="100%">
                    <tbody>
                        <tr>
                            <td class="text-center" colspan="4">
                                @if ($staff->image != null)
                                    <img src="{{ $staff->image ? asset('storage/users/' . $staff->image) : asset('assets/media/svg/files/blank-image.svg') }}"
                                        class="me-3" alt="" width="50px">
                                @else
                                    <img src="{{ asset('uploads/images/' . $staff->image) }}" width="50px" alt="">
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">{{ _lang('Name') }}</td>
                            <td colspan="2">{{ $staff->name }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Designation') }}</td>
                            <td>{{ $staff->designation }}</td>
                            <td>{{ _lang('Date Of Birth') }}</td>
                            <td>{{ $staff->birthday }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Gender') }}</td>
                            <td>{{ $staff->gender }}</td>
                            <td>{{ _lang('Religion') }}</td>
                            <td>{{ $staff->religion }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Joining Date') }}</td>
                            <td>{{ $staff->joining_date }}</td>
                            <td>Address</td>
                            <td>{{ $staff->address }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Phone') }}</td>
                            <td>{{ $staff->phone }}</td>
                            <td>{{ _lang('Email') }}</td>
                            <td>{{ $staff->email }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Blood Group') }}</td>
                            <td>{{ $staff->blood }}</td>
                            <td>{{ _lang('SL') }}</td>
                            <td>{{ $staff->sl }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
