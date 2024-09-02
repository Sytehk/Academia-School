@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Accounting Category') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-6">
            <form action="{{ route('accounting-categories.update', $accountingCategory->id) }}" autocomplete="off"
                method="post">
                @csrf
                @method('PATCH')
                <div class="form-group">
                    <div class="col-sm-12">
                        <label class="control-label my-2">{{ _lang('Name') }}</label>
                        <input type="text" class="form-control" name="name" value="{{ $accountingCategory->name }}"
                            required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-12">
                        <label class="control-label my-2">{{ _lang('Code') }}</label>
                        <input type="number" class="form-control" min="1" name="code" required
                            value="{{ $accountingCategory->code }}">
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-12">
                        {!! generate_select(
                            'type',
                            Modules\Accounting\Models\AccountingType::getAccountingTypes(),
                            _lang('Type'),
                            $accountingCategory->type,
                            true,
                            _lang('--Select Type--'),
                            'form-control',
                        ) !!}
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
@endsection
