@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Banners') }}
@endsection

@section('create-button')
    <a href="{{ route('banners.create') }}" class="btn btn-sm fw-bold btn-primary">

        {{ _lang('Add New Banner') }}

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
                            <th>{{ _lang('Title') }}</th>
                            <th>{{ _lang('Image') }}</th>
                            <th>{{ _lang('button_name') }}</th>
                            <th>{{ _lang('button_link') }}</th>
                            <th>{{ _lang('description') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($banners as $banner)
                            <tr>
                                <td>{{ $banner->id }}</td>
                                <td>
                                    {{ $banner->title }}
                                </td>
                                <td>
                                    @if ($banner->image)
                                        <img src="{{ $banner->image ? asset('storage/banners/' . $banner->image) : asset('assets/media/svg/files/blank-image.svg') }}"
                                            class="me-3" alt="" width="50px">
                                    @else
                                        <img src="{{ asset('images/no-image.png') }}" width="40" height="40">
                                    @endif
                                </td>
                                <td>
                                    {{ $banner->button_name }}
                                </td>
                                <td>
                                    {{ $banner->button_link }}
                                </td>
                                <td>
                                    {{ Str::limit($banner->description, 20) }}
                                </td>
                                <td>
                                    <form action="{{ route('banners.destroy', $banner->id) }}" method="POST">
                                        <a href="{{ route('banners.edit', $banner->id) }}" class="btn btn-warning btn-sm">
                                            Edit
                                        </a>
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm">
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
