@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Picklist') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default">
                <div class="card-header">{{ _lang('View Picklist') }}</div>

                <div class="">
                    <table class="table table-bordered">
                        <tr>
                            <td>{{ _lang('Type') }}</td>
                            <td>{{ $picklist->type }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Value') }}</td>
                            <td>{{ $picklist->value }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
