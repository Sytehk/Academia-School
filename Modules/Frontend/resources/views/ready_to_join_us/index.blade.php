@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Ready To Join Us') }}
@endsection

@section('create-button')
    <a href="{{ route('ready-to-join-us.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add New') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ _lang('Icon') }}</th>
                            <th>{{ _lang('Title') }}</th>
                            <th>{{ _lang('Description') }}</th>
                            <th>{{ _lang('Button') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($readyToJoinUs as $item)
                            <tr>
                                <td>{{ $item->id }}</td>

                                <td>
                                    @if ($item->icon)
                                        <img src="{{ asset('storage/ready_to_join_us/' . $item->icon) }}" width="40"
                                            height="40">
                                    @else
                                        <img src="{{ asset('images/no-image.png') }}" width="40" height="40">
                                    @endif
                                </td>

                                <td>{{ $item->title }}</td>

                                <td>{{ \Str::limit($item->description, 50) }}</td>

                                <td>
                                    @if ($item->button_name)
                                        <a href="{{ $item->button_link }}" class="btn btn-sm btn-info">
                                            {{ $item->button_name }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('ready-to-join-us.edit', $item->id) }}"
                                        class="btn btn-sm btn-warning">
                                        Edit
                                    </a>

                                    <form action="{{ route('ready-to-join-us.destroy', $item->id) }}" method="POST"
                                        style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                            Delete
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
