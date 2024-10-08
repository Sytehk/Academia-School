@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Dashboard') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default" data-collapsed="0">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('My Library History') }}
                    </div>
                </div>
                <div class="">
                    @if (!empty($issues))
                        <div class="col-lg-12">
                            <div class="card-header text-center">
                                <div class="card-title">
                                    {{ _lang('Book Issues Of') }}<br>
                                    {{ _lang('Member Name - ') }}{{ $member->name }}<br>
                                    {{ _lang('Library Id - ') }}{{ $member->library_id }}<br>
                                </div>
                            </div>

                            <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                                <thead>
                                    <tr>
                                        <th>{{ _lang('SL No.') }}</th>
                                        <th>{{ _lang('Book Name') }}</th>
                                        <th>{{ _lang('Category') }}</th>
                                        <th>{{ _lang('Issue Date') }}</th>
                                        <th>{{ _lang('Due Date') }}</th>
                                        <th>{{ _lang('Return Date') }}</th>
                                        <th>{{ _lang('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($issues as $data)
                                        <tr>
                                            <td>{{ $data->code }}</td>
                                            <td>{{ $data->book_name }}</td>
                                            <td>{{ $data->category }}</td>
                                            <td>{{ date('d/ M/ Y', strtotime($data->issue_date)) }}</td>
                                            <td>{{ date('d/ M/ Y', strtotime($data->due_date)) }}</td>
                                            <td>
                                                @if ($data->return_date != '')
                                                    {{ date('d-M-Y', strtotime($data->return_date)) }}
                                                @endif
                                            </td>
                                            <td>
                                                @if ($data->status == '1')
                                                    <span class="badge badge-danger">{{ _lang('Not Return Yet') }}</span>
                                                @else
                                                    <span class="badge badge-success">{{ _lang('Returned') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <h5 class="text-center">{{ _lang('Sorry, No Records Found !') }}</h5>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
