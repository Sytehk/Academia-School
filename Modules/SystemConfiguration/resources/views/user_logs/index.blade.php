@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('User Activities') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default" data-collapsed="0">
                <div class="card-header">
                    <span class="card-title">
                        {{ _lang('User Logs List') }}
                    </span>
                </div>
                <div class="table-responsive mt-5">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                        <thead>
                            <th>{{ _lang('SL No.') }}</th>
                            <th>{{ _lang('Name') }}</th>
                            <th>{{ _lang('IP Address') }}</th>
                            <th>{{ _lang('Action') }}</th>
                            <th>{{ _lang('Detail') }}</th>
                            {{-- <th>{{ _lang('Model') }}</th> --}}
                            <th>{{ _lang('Create Time') }}</th>
                            <th>{{ _lang('Update Time') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($userLogs as $data)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td>{{ $data->user?->name }}</td>
                                    <td>{{ $data->ip_address }}</td>
                                    <td>{{ $data->action }}</td>
                                    <td>{{ $data->detail }}</td>
                                    {{-- <td>{{ $data->model }}</td> --}}
                                    <td>
                                        {{ $data->created_at->format('H:m:s - d-M,Y') }}
                                    </td>
                                    <td>
                                        {{ $data->updated_at->format('H:m:s - d-M,Y') }}
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
