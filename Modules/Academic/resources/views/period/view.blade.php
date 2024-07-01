@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Period') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default">
                <div class="card-header">{{ _lang('View Period') }}</div>

                <div class="">
                    <table class="table table-bordered">
                        <tr>
                            <td>{{ _lang('Payment Method Name') }}</td>
                            <td>{{ $paymentmethod->name }}</td>
                        </tr>

                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
