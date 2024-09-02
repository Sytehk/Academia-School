@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Accounting Leadger') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="table-responsive border p-5">
                <div class="card-header">
                    <span class="card-title">
                        {{ _lang('Add New Ledger') }}
                    </span>
                </div>

                @include('accounting::accounting_ledgers.form')
            </div>
        </div>

        <div class="col-lg-8">
            <div class="table-responsive border p-5">
                <div class="card-header">
                    <span class="card-title">
                        {{ _lang('Accounting Ledger List') }}
                    </span>
                </div>
                <div class="table-responsive mt-5">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>{{ _lang('SL No.') }}</th>
                            <th>{{ _lang('Ledger Name') }}</th>
                            <th>{{ _lang('Account Group') }}</th>
                            <th>{{ _lang('Account Category') }}</th>
                            <th>{{ _lang('Nature') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($accountingLedgers as $accountingLedger)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td>{{ $accountingLedger->ledger_name }}</td>
                                    <td>{{ $accountingLedger->accountingGroup->name ?? '' }}</td>
                                    <td>{{ $accountingLedger->accountingCategory->name ?? '' }}</td>
                                    <td>{{ $accountingLedger->accountingCategory->nature ?? '' }}</td>
                                    <td>
                                        <form action="{{ route('accounting-ledgers.destroy', $accountingLedger->id) }}"
                                            method="post">
                                            <a href="{{ route('accounting-ledgers.edit', $accountingLedger->id) }}"
                                                class="btn btn-warning btn-sm"><i class="fa fa-pencil"
                                                    aria-hidden="true"></i></a>
                                            @method('DELETE')
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm btn-remove"><i
                                                    class="fa fa-trash" aria-hidden="true"></i></button>
                                        </form>


                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
