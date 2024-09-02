@extends('layouts.backend')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card border">
                <div class="card-header">
                    <h4 class="text-center">{{ _lang('Balance Sheet') }}</h4>
                </div>
            </div>

                <div class="card-border">
                    <div class="card-header py-3 d-flex justify-content-between">

                        <div>
                            <p class="">Total Found: <strong>{{ count($account_details) ?? 0 }}</strong></p>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered data-table">
                            <thead>
                                <th>{{ _lang('Date') }}</th>
                                <th>{{ _lang('Voucher ID') }}</th>
                                <th>{{ _lang('Debit') }}</th>
                                <th>{{ _lang('Credit') }}</th>
                            </thead>
                            <tbody>
                                @php
                                    $totalDebit = 0;
                                    $totalCredit = 0;
                                @endphp
                                @foreach ($account_details as $detail)
                                    <tr>
                                        <td>{{ $detail->created_at }}</td>
                                        <td>{{ $detail->accountTransaction->voucher_id??'N/A' }}</td>

                                        <td class="text-right">
                                                {{ $detail->debit??0 }}
                                        </td>
                                        <td class="text-right">
                                                {{ $detail->credit??0 }}
                                        </td>
                                        @php
                                            $totalDebit += $detail->debit??0;
                                            $totalCredit += $detail->credit??0;
                                        @endphp
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr class="bg-primary">
                                    <td colspan="2" class="text-right"> <strong
                                            class="text-white">{{ _lang('Total') }}</strong> </td>
                                    <td class="text-right"> <strong class="text-white">{{ format_currency($totalDebit) }} </strong>
                                    </td>
                                    <td class="text-right"> <strong class="text-white">{{ format_currency($totalCredit) }} </strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
        </div>
    </div>
@endsection
