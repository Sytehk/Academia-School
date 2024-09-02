@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Accounting Fund') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Update Group') }}
                    </div>
                </div>
                <div class="">
                    <form action="{{ route('accounting-funds.update', $accountingFund->id) }}" autocomplete="off"
                        method="post">
                        @csrf
                        @method('PATCH')
                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Name') }}</label>
                                <input type="text" class="form-control" name="name"
                                    value="{{ $accountingFund->name }}" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Serial') }}</label>
                                <input type="number" class="form-control" min="1" name="serial" required
                                    value="{{ $accountingFund->serial }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Update') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
