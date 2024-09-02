@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Accounting Group') }}
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
                    <form action="{{ route('accounting-groups.update', $accountingGroup->id) }}" autocomplete="off"
                        method="post">
                        @csrf
                        @method('PATCH')

                        <div class="form-group">
                            <div class="col-sm-12">
                                {!! generate_select(
                                    'accounting_category_id',
                                    Modules\Accounting\Models\AccountingCategory::orderBy('name', 'asc')->pluck('name', 'id')->toArray(),
                                    _lang('Account Category'),
                                    old('accounting_category_id', $accountingGroup->accounting_category_id),
                                    true,
                                    _lang('--Select Category--'),
                                    'form-select form-select-sm form-select-solid',
                                ) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Name') }}</label>
                                <input type="text" class="form-control" name="name"
                                    value="{{ $accountingGroup->name }}" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Code') }}</label>
                                <input type="number" class="form-control" min="1" name="code" required
                                    value="{{ $accountingGroup->code }}">
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
