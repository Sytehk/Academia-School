@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Edit Payment Gateway') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4>
                        {{ strtoupper($gateway->name) }}
                        Gateway Configuration
                    </h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('saas-payment-gateways.update', $gateway->id) }}">
                        @csrf
                        @method('PATCH')

                        <div class="row">

                            <div class="col-lg-4">
                                <label>
                                    Gateway Name
                                </label>
                                <input type="text" class="form-control" value="{{ strtoupper($gateway->name) }}" readonly>
                            </div>

                            <div class="col-lg-4">
                                <label>
                                    Mode
                                </label>
                                <select name="mode" class="form-select">
                                    <option value="test" {{ $gateway->mode == 'test' ? 'selected' : '' }}>
                                        Test
                                    </option>
                                    <option value="live" {{ $gateway->mode == 'live' ? 'selected' : '' }}>
                                        Live
                                    </option>
                                </select>
                            </div>

                            <div class="col-lg-4">
                                <label>
                                    Status
                                </label>
                                <select name="status" class="form-select">
                                    <option value="1" {{ $gateway->status ? 'selected' : '' }}>
                                        Active
                                    </option>

                                    <option value="0" {{ !$gateway->status ? 'selected' : '' }}>
                                        Inactive
                                    </option>
                                </select>
                            </div>

                        </div>

                        <hr>

                        <h5 class="mb-4">
                            Payment Information
                        </h5>

                        <div class="row">

                            @foreach ($payment_info as $key => $value)
                                <div class="col-lg-6 mb-4">
                                    <label>
                                        {{ ucwords(str_replace('_', ' ', $key)) }}
                                    </label>
                                    <input type="text" class="form-control" name="payment_info[{{ $key }}]"
                                        value="{{ $value }}">

                                </div>
                            @endforeach

                        </div>

                        <button class="btn btn-primary mt-4">
                            Update
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
