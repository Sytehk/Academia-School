@extends('layouts.pdf.index')
@section('styles')
    <link href="{{ asset('backend') }}/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: rgb(214, 212, 212)
        }

        header {
            height: 153px !important;
        }

        .form-view {
            width: 1122px !important;
        }
    </style>
@endsection
@section('content-download')
    <div class="noprint print-download-buttons">
        @include('layouts.pdf.back-button')
        @include('layouts.pdf.print-button')

    </div>
    <div style="clear: both;"></div>
@endsection
@section('content')
    @if (isset($issues) && !empty($issues))
        @include('layouts.pdf.header')

        <div class="card-border">
            <div class="card-header py-3 d-flex justify-content-between">
                <div>
                    <p>Total Book Issue List</p>
                </div>
            </div>
            <div class="card-body">
                <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                    <thead>
                        <th>Code</th>
                        <th>{{ _lang('Book Name') }}</th>
                        <th>{{ _lang('Type') }}</th>
                        <th>{{ _lang('Library ID') }}</th>
                        <th>{{ _lang('Name') }}</th>
                        <th>{{ _lang('Phone') }}</th>
                        <th>{{ _lang('Issue Date') }}</th>
                        <th>{{ _lang('Return Date') }}</th>
                        <th>{{ _lang('Received Date') }}</th>
                        <th>{{ _lang('Fine') }}</th>
                        <th>{{ _lang('Status') }}</th>
                    </thead>

                    <tbody>

                        @foreach ($issues as $data)
                            <tr>
                                <td>{{ $data->code }}</td>
                                <td>{{ $data->book_name }}</td>
                                <td>{{ $data->type }}</td>
                                <td>
                                    {{ $data->library_id }}
                                </td>
                                <td>
                                    @if (!empty($data->student_id))
                                        <span style="text-transform: capitalize">{{ $data->student_first_name }}</span>
                                        <span style="text-transform: capitalize">{{ $data->student_last_name }}</span>
                                    @elseif (!empty($data->teacher_id))
                                        <span style="text-transform: capitalize">{{ $data->teacher_name }}</span>
                                    @else
                                        <span style="text-transform: capitalize">{{ $data->staff_name }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if (!empty($data->student_id))
                                        {{ $data->student_phone }}
                                    @elseif (!empty($data->teacher_id))
                                        {{ $data->teacher_phone }}
                                    @else
                                        {{ $data->staff_phone }}
                                    @endif
                                </td>
                                <td>{{ $data->issue_date }}</td>
                                <td>{{ $data->due_date }}</td>
                                <td>{{ $data->return_date }}</td>
                                <td>{{ $data->total_fine }}</td>
                                <td>
                                    @if ($data->status == '1')
                                        <span class="badge badge-danger">{{ _lang('Open') }}</span>
                                    @else
                                        <span class="badge badge-success">{{ 'Returned' }}</span>
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot class="bg-primary">
                        <tr>
                            <td colspan="6" style="text-align:right; color: white;">{{ _lang('Total') }}</td>
                            <td colspan="5" style="text-align:left; color: white;">{{ count($issues) }}</td>
                        </tr>
                    </tfoot>
                </table>

                <table>
                    <tr>
                        <td style="text-align:left; border: none;">Powered by {{ config('app.name') }} </td>
                        @php
                            $currentDate = date('M d, Y');
                        @endphp
                        <td style="text-align:right; border: none;">{{ $currentDate }}</td>
                    </tr>
                </table>
            </div>
        </div>
    @endif
@endsection
