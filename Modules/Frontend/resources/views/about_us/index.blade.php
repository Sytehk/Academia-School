@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('About Us') }}
@endsection

@section('create-button')
    <a href="{{ route('about-us.create') }}" class="btn btn-sm fw-bold btn-primary">

        {{ _lang('Add About Us') }}

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
                            <th>{{ _lang('Short Description') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($aboutUs as $about)
                            <tr>
                                <td>{{ $about->id }}</td>
                                <td>
                                    {{ $about->title }}
                                </td>
                                <td>
                                    @if ($about->image)
                                        <img src="{{ asset('storage/about_us/' . $about->image) }}" class="me-3"
                                            alt="" width="50px">
                                    @else
                                        <img src="{{ asset('images/no-image.png') }}" width="40" height="40">
                                    @endif
                                </td>
                                <td>
                                    {{ Str::limit($about->description, 20) }}
                                </td>
                                <td>
                                    <form action="{{ route('about-us.destroy', $about->id) }}" method="POST">
                                        <a href="{{ route('about-us.edit', $about->id) }}" class="btn btn-warning btn-sm">
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
