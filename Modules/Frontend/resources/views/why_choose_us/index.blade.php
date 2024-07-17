@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Why Choose Us') }}
@endsection

@section('create-button')
    <a href="{{ route('why-choose-us.create') }}" class="btn btn-sm fw-bold btn-primary">
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
                            <th>Title</th>
                            <th>Icon</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($whyChooseUs as $item)
                            <tr>
                                <td>{{ $item->id }}</td>

                                <td>{{ $item->title }}</td>

                                <td>
                                    @if ($item->icon)
                                        <img src="{{ asset('storage/why_choose_us/' . $item->icon) }}" width="40"
                                            height="40">
                                    @else
                                        <img src="{{ asset('images/no-image.png') }}" width="40" height="40">
                                    @endif
                                </td>

                                <td>
                                    {{ Str::limit($item->description, 50) }}
                                </td>

                                <td>
                                    <form action="{{ route('why-choose-us.destroy', $item->id) }}" method="POST">

                                        <a href="{{ route('why-choose-us.edit', $item->id) }}"
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
