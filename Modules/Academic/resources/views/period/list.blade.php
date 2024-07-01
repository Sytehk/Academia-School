@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Periods') }}
@endsection

@section('create-button')
    <a href="{{ route('periods.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add New Period') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('SL No') }}</th>
                            <th>{{ _lang('Period') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($periods->sortBy('serial_no') as $period)
                            <tr>
                                <td>{{ $period->serial_no }}</td>
                                <td>{{ $period->period }}</td>
                                <td>
                                    <form action="{{ route('periods.destroy', $period->id) }}" method="post">
                                        <a href="{{ route('periods.edit', $period->id) }}"
                                            data-title="{{ _lang('Update Period') }}"
                                            class="btn btn-warning btn-sm">{{ _lang('Edit') }}</a>

                                        @csrf
                                        <input name="_method" type="hidden" value="DELETE">
                                        <button class="btn btn-danger btn-sm btn-remove"
                                            type="submit">{{ _lang('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
