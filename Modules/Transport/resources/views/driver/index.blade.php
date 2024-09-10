@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Drivers') }}
@endsection

@section('create-button')
    <a href="{{ route('drivers.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add New Driver') }}
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
                            <th>{{ _lang('Name') }}</th>
                            <th>{{ _lang('Phone') }}</th>
                            <th>{{ _lang('License No') }}</th>
                            <th>{{ _lang('Assigned Bus') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($drivers as $key => $driver)
                            <tr>
                                <td>{{ $key + 1 }}</td>

                                <td>{{ $driver->name }}</td>

                                <td>{{ $driver->phone_number }}</td>

                                <td>{{ $driver->license_number }}</td>

                                <td>
                                    {{ $driver->bus_number ?? '-' }}
                                </td>

                                <td>
                                    @if ($driver->status == 1)
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
                                    <a href="{{ route('drivers.edit', $driver->id) }}" class="btn btn-sm btn-primary">
                                        {{ _lang('Edit') }}
                                    </a>

                                    <form action="{{ route('drivers.destroy', $driver->id) }}" method="POST"
                                        class="d-inline">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Delete this driver?')">
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
