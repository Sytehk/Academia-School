@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Accounting Groups') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="table-responsive border p-5">
                <div class="card-header">
                    <span class="card-title">
                        {{ _lang('Add New Group') }}
                    </span>
                </div>
                <div class="">
                    <form action="{{ route('accounting-groups.store') }}"method="post">
                        @csrf

                        <div class="form-group">
                            <div class="col-sm-12">
                                {!! generate_select(
                                    'accounting_category_id',
                                    Modules\Accounting\Models\AccountingCategory::orderBy('name', 'asc')->pluck('name', 'id')->toArray(),
                                    _lang('Account Category'),
                                    get_old_value(null, 'accounting_category_id'),
                                    true,
                                    _lang('--Select Category--'),
                                    'form-select form-select-sm form-select-solid',
                                ) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Accouting Group Name') }}</label>
                                <input type="text" class="form-control" name="name" required
                                    value="{{ old('name') }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-5">
                                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="table-responsive border p-5">
                <div class="card-header">
                    <span class="card-title">
                        {{ _lang('Accounting Group List') }}
                    </span>
                </div>
                <div class="table-responsive mt-5">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>{{ _lang('SL No.') }}</th>
                            <th>{{ _lang('Group') }}</th>
                            <th>{{ _lang('Name') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($accountingGroups as $accountingGroup)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td>{{ $accountingGroup->accountingCategory->name ?? '' }}</td>
                                    <td>{{ $accountingGroup->name }}</td>
                                    <td>
                                        <form action="{{ route('accounting-groups.destroy', $accountingGroup->id) }}"
                                            method="post">
                                            <a href="{{ route('accounting-groups.edit', $accountingGroup->id) }}"
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
@endsection
