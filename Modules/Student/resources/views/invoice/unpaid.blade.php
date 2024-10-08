@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Dashboard') }}
@endsection
@section('styles')
    <link href="{{ asset('backend') }}/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: rgb(214, 212, 212)
        }

        header {
            height: 153px !important;
        }
    </style>
@endsection
@section('content')
    @if (isset($unpaidInfos) && !empty($unpaidInfos) && count($unpaidInfos) > 0)
        <div class="card-border">
            <div class="card-header py-3 d-flex justify-content-between">
                <div>
                    <p class="">Total Found: <strong>{{ count($unpaidInfos) }}</strong></p>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                    <thead>
                        <th>{{ _lang('Student ID') }}</th>
                        <th>{{ _lang('Student Name') }}</th>
                        <th>{{ _lang('Roll') }}</th>
                        <th>{{ _lang('Due Details') }}</th>
                        <th>{{ _lang('Fee Due') }}</th>
                        <th>{{ _lang('Payslip due') }}</th>
                        <th>{{ _lang('Total Due') }}</th>
                    </thead>
                    <tbody>
                        @foreach ($unpaidInfos as $data)
                            @if ($data->student_id === get_student_id())
                                <tr>
                                    <td class="text-right">{{ $data->student_id }}</td>
                                    <td class="text-right">{{ $data->first_name }}</td>
                                    <td class="text-right">{{ $data->roll }}</td>
                                    <td class="text-right">{{ $data->due_details }}</td>
                                    <td class="text-right">{{ $data->total_fee_due }}</td>
                                    <td class="text-right">{{ $data->total_slip_due }}</td>
                                    <td class="text-right">{{ $data->total_fee_due + $data->total_slip_due }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

@endsection
