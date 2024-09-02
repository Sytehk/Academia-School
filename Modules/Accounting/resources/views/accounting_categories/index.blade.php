@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Accounting Category') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="table-responsive border p-5">
                <div class="card-header">
                    <span class="card-title">
                        {{ _lang('Add New Category') }}
                    </span>
                </div>
                <div class="">
                    <form action="{{ route('accounting-categories.store') }}"method="post">
                        @csrf

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Accouting Category Name') }}</label>
                                <input type="text" class="form-control" name="name" required
                                    value="{{ old('name') }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <label class="control-label my-2">{{ _lang('Code') }}</label>
                                <input type="number" class="form-control" name="code" min="1"
                                    value="{{ old('code') }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                {!! generate_select(
                                    'type',
                                    Modules\Accounting\Models\AccountingType::getAccountingTypes(),
                                    _lang('Type'),
                                    old('type'),
                                    true,
                                    _lang('--Select Type--'),
                                    'form-control',
                                ) !!}
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
                        {{ _lang('Accounting Category List') }}
                    </span>
                </div>
                <div class="table-responsive mt-5">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>{{ _lang('SL No.') }}</th>
                            <th>{{ _lang('Name') }}</th>
                            <th>{{ _lang('Code') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($accountingCategories as $accountingCategory)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td>{{ $accountingCategory->name }}</td>
                                    <td>{{ $accountingCategory->code }}</td>
                                    <td>
                                        <form
                                            action="{{ route('accounting-categories.destroy', $accountingCategory->id) }}"
                                            method="post">
                                            @csrf
                                            @method('DELETE')

                                            <a href="{{ route('accounting-categories.edit', $accountingCategory->id) }}"
                                                class="btn btn-warning btn-sm"><i class="fa fa-pencil"
                                                    aria-hidden="true"></i></a>
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
