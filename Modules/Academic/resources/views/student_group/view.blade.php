@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Student Group') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default">
                <div class="card-header">{{ _lang('View Student Group') }}</div>

                <div class="">
                    <table class="table table-bordered">
                        <tr>
                            <td>{{ _lang('Group Name') }}</td>
                            <td>{{ $studentgroup->group_name }}</td>
                        </tr>

                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
