@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Accounting Leadger') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Update Ledger') }}
                    </div>
                </div>
                <div class="">
                    @include('accounting::accounting_ledgers.form', [
                        'accountingLedger' => $accountingLedger,
                        'id' => $accountingLedger->id,
                        'accountingCategories' => $accountingCategories,
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection
