@extends('layouts.backend')


@section('page_title_breadcrumb')
    {{ _lang('Payment Gateways') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('Gateway') }}</th>
                            <th>{{ _lang('Mode') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($gateways as $gateway)
                            <tr>
                                <td>
                                    <strong>
                                        {{ strtoupper($gateway->name) }}
                                    </strong>
                                </td>

                                <td>
                                    @if ($gateway->mode == 'test')
                                        <span class="badge bg-warning">
                                            TEST
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            LIVE
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if ($gateway->status)
                                        <span class="badge bg-success">
                                            Active
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('saas-payment-gateways.edit', $gateway->id) }}"
                                        class="btn btn-sm btn-warning">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach

                    </tbody>

                </table>
            </div>
        </div>
    </div>
@endsection
