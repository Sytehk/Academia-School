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
            width: 770px;
        }
    </style>
@endsection
@section('content-download')
    <div class="noprint print-download-buttons">
        @include('layouts.pdf.back-button')
        @include('layouts.pdf.download-button')
        @include('layouts.pdf.print-button')

    </div>
    <div style="clear: both;"></div>
@endsection
@section('content')
    @include('layouts.pdf.header')
    <table class="table data-table table-striped border">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Name</th>
                <th>Paid Status</th>
                <th>Payment Type</th>
                <th>Net Salary</th>


                <th>Paid Amount</th>
                <th>Due Amount</th>
                <th>Advance Amount</th>
                <th>Payment Date</th>
            </tr>
        </thead>
        <tbody>
            <!-- Add table body (if needed) -->
            @foreach ($data as $item)
                <tr>

                    <td>{{ $item['user_id'] }}</td>
                    <td>{{ $item['user']->name }}</td>
                    <td>
                        @if ($item['is_paid'] == 0)
                            Unpaid
                        @else
                            Paid
                        @endif
                    </td>
                    <td>{{ $item['type'] }}</td>
                    <td>{{ $item['net_salary'] }}</td>

                    <td>{{ $item['amount'] }}</td>
                    <td>{{ $item['current_due'] }}</td>
                    <td>{{ $item['current_advance'] }}</td>
                    <td>{{ $item['payment_date'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
