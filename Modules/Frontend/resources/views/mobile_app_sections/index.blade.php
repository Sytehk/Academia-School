@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Mobile App Sections') }}
@endsection

@section('create-button')
    <a href="{{ route('mobile-app-sections.create') }}" class="btn btn-sm fw-bold btn-primary">
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
                            <th>Heading</th>
                            <th>Image</th>
                            <th>Features</th>
                            <th>Store Links</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($mobileAppSections as $item)
                            <tr>
                                <td>{{ $item->id }}</td>

                                <td>{{ $item->title }}</td>

                                <td>{{ $item->heading }}</td>

                                <td>
                                    @if ($item->image)
                                        <img src="{{ asset('storage/mobile_app_sections/' . $item->image) }}" width="60">
                                    @else
                                        <img src="{{ asset('images/no-image.png') }}" width="40" height="40">
                                    @endif
                                </td>

                                <td>
                                    <ul class="mb-0">
                                        @if ($item->feature_one)
                                            <li>{{ $item->feature_one }}</li>
                                        @endif
                                        @if ($item->feature_two)
                                            <li>{{ $item->feature_two }}</li>
                                        @endif
                                        @if ($item->feature_three)
                                            <li>{{ $item->feature_three }}</li>
                                        @endif
                                    </ul>
                                </td>

                                <td>
                                    @if ($item->play_store_link)
                                        <a href="{{ $item->play_store_link }}" target="_blank">Play Store</a><br>
                                    @endif

                                    @if ($item->app_store_link)
                                        <a href="{{ $item->app_store_link }}" target="_blank">App Store</a>
                                    @endif
                                </td>

                                <td>
                                    <form action="{{ route('mobile-app-sections.destroy', $item->id) }}" method="POST">
                                        <a href="{{ route('mobile-app-sections.edit', $item->id) }}"
                                            class="btn btn-warning btn-sm">Edit</a>

                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-danger btn-sm">Delete</button>
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
