@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Academic Images') }}
@endsection

@section('create-button')
    <a href="{{ route('gallery-images.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add New Image') }}
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
                            <th>{{ _lang('Heading') }}</th>
                            <th>{{ _lang('Image') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($academicImages as $item)
                            <tr>
                                <td>{{ $item->id }}</td>

                                <td>{{ $item->title }}</td>

                                <td>{{ Str::limit($item->heading, 30) }}</td>

                                <td>
                                    @if ($item->image)
                                        <img src="{{ asset('storage/academic_images/' . $item->image) }}" width="50">
                                    @else
                                        <img src="{{ asset('images/no-image.png') }}" width="40" height="40">
                                    @endif
                                </td>

                                <td>
                                    @if ($item->status == 1)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>

                                <td>
                                    <form action="{{ route('gallery-images.destroy', $item->id) }}" method="POST">

                                        <a href="{{ route('gallery-images.edit', $item->id) }}"
                                            class="btn btn-warning btn-sm">
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
