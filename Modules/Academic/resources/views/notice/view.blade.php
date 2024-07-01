@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Notice') }}
@endsection
@section('create-button')
    <a class="btn btn-sm fw-bold btn-primary" href="{{ route('notices.index') }}">
        {{ _lang('Notice List') }}
    </a>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered">
                <tr>
                    <td>
                        <h4>{{ $notice->heading }}</h4>
                    </td>
                </tr>
                <tr>
                    <td class="details-td">{!! $notice->content !!}</td>
                </tr>
                <tr>
                    <td>{{ _lang('Notice Can See Only') }} : {{ object_to_string($notice->userType, 'user_type') }}
                    </td>
                </tr>

            </table>
        </div>
    </div>
@endsection
