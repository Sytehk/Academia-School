@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Fee Type') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default">
                <div class="card-header">{{ _lang('View Fee Type') }}</div>

                <div class="">
                    <table class="table table-bordered">
                        <tr>
                            <td>{{ _lang('Fee Type') }}</td>
                            <td>{{ $feetype->fee_type }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Fee Code') }}</td>
                            <td>{{ $feetype->fee_code }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Note') }}</td>
                            <td>{{ $feetype->note }}</td>
                        </tr>

                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
