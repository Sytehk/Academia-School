@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Academic/Session Year') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default">
                <div class="card-header">{{ _lang('View Academic Year') }}</div>

                <div class="">
                    <table class="table table-bordered">
                        <tr>
                            <td>{{ _lang('Session') }}</td>
                            <td>{{ $academicYear->session }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Year') }}</td>
                            <td>{{ $academicYear->year }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
