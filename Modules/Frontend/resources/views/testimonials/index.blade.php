@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Testimonials') }}
@endsection

@section('create-button')
    <a href="{{ route('testimonials.create') }}" class="btn btn-sm fw-bold btn-primary">
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
                            <th>{{ _lang('Image') }}</th>
                            <th>{{ _lang('Name') }}</th>
                            <th>{{ _lang('Rating') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($testimonials as $item)
                            <tr>
                                <td>{{ $item->id }}</td>

                                <td>
                                    @if ($item->thumbnail_image)
                                        <img src="{{ asset('storage/testimonials/' . $item->thumbnail_image) }}"
                                            width="50" height="50" style="border-radius:50%;">
                                    @else
                                        <img src="{{ asset('images/no-image.png') }}" width="50" height="50">
                                    @endif
                                </td>

                                <td>{{ $item->name }}</td>

                                <td>
                                    {{ $item->ratting }} / 5
                                </td>

                                <td>
                                    @if ($item->status == 1)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('testimonials.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                        Edit
                                    </a>

                                    <form action="{{ route('testimonials.destroy', $item->id) }}" method="POST"
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
