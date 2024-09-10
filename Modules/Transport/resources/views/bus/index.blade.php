@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Buses') }}
@endsection

@section('create-button')
    <a href="{{ route('buses.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add New Bus') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                    <thead>
                        <tr>
                            <th>{{ _lang('SL No.') }}</th>
                            <th>{{ _lang('Bus Number') }}</th>
                            <th>{{ _lang('Model') }}</th>
                            <th>{{ _lang('Capacity') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($buses as $key => $bus)
                            <tr>
                                <td>{{ $key + 1 }}</td>

                                <td>{{ $bus->bus_number }}</td>

                                <td>{{ $bus->model ?? '-' }}</td>

                                <td>{{ $bus->capacity }}</td>

                                <td>
                                    @if ($bus->status)
                                        <span class="badge bg-success">
                                            {{ _lang('Active') }}
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            {{ _lang('Inactive') }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('buses.edit', $bus->id) }}" class="btn btn-sm btn-primary">
                                        {{ _lang('Edit') }}
                                    </a>

                                    <form action="{{ route('buses.destroy', $bus->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Delete this bus?')">
                                            {{ _lang('Delete') }}
                                        </button>
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
