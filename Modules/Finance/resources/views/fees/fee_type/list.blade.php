@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Fee Type') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default no-export">
                <div class="card-header"><span class="card-title">{{ _lang('List Fee Type') }}</span>
                    <a class="btn btn-primary btn-sm pull-right ajax-modal" data-title="{{ _lang('Add Fee Type') }}"
                        href="{{ route('fee_types.create') }}">{{ _lang('Add New') }}</a>
                </div>

                <div class="table-responsive mt-5">
                    @if (\Session::has('success'))
                        <div class="alert alert-success">
                            <p>{{ \Session::get('success') }}</p>
                        </div>
                        <br />
                    @endif
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <tr>
                                <th>{{ _lang('Fee Type') }}</th>
                                <th>{{ _lang('Fee Code') }}</th>
                                <th>{{ _lang('Note') }}</th>
                                <th>{{ _lang('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($feetypes as $feetype)
                                <tr id="row_{{ $feetype->id }}">
                                    <td class='fee_type'>{{ $feetype->fee_type }}</td>
                                    <td class='fee_code'>{{ $feetype->fee_code }}</td>
                                    <td class='note'>{{ $feetype->note }}</td>

                                    <td>
                                        <form action="{{ action('FeeTypeController@destroy', $feetype['id']) }}"
                                            method="post">
                                            <a href="{{ action('FeeTypeController@edit', $feetype['id']) }}"
                                                data-title="{{ _lang('Update Fee Type') }}"
                                                class="btn btn-warning btn-sm ajax-modal">{{ _lang('Edit') }}</a>
                                            <a href="{{ action('FeeTypeController@show', $feetype['id']) }}"
                                                data-title="{{ _lang('View Fee Type') }}"
                                                class="btn btn-primary btn-sm ajax-modal">{{ _lang('View') }}</a>
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
    </div>
@endsection
