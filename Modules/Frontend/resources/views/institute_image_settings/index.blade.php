@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Institute Image Settings') }}
@endsection

@section('create-button')
    <a href="{{ route('institute-image-settings.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add New') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <div class="table-responsive">

                <table class="table align-middle table-row-dashed fs-6 gy-5">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Header Light</th>
                            <th>Header Dark</th>
                            <th>Footer Light</th>
                            <th>Footer Dark</th>
                            <th>Favicon</th>
                            <th>Status</th>
                            <th width="150">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($instituteImages as $item)
                            <tr>
                                <td>{{ $item->id }}</td>

                                <td>
                                    @if ($item->header_logo_light_theme)
                                        <img src="{{ asset('storage/institute_image_settings/' . $item->header_logo_light_theme) }}"
                                            width="60">
                                    @endif
                                </td>

                                <td>
                                    @if ($item->header_logo_dark_theme)
                                        <img src="{{ asset('storage/institute_image_settings/' . $item->header_logo_dark_theme) }}"
                                            width="60">
                                    @endif
                                </td>

                                <td>
                                    @if ($item->footer_logo_light_theme)
                                        <img src="{{ asset('storage/institute_image_settings/' . $item->footer_logo_light_theme) }}"
                                            width="60">
                                    @endif
                                </td>

                                <td>
                                    @if ($item->footer_logo_dark_theme)
                                        <img src="{{ asset('storage/institute_image_settings/' . $item->footer_logo_dark_theme) }}"
                                            width="60">
                                    @endif
                                </td>

                                <td>
                                    @if ($item->favicon)
                                        <img src="{{ asset('storage/institute_image_settings/' . $item->favicon) }}"
                                            width="80">
                                    @endif
                                </td>

                                <td>
                                    @if ($item->status == 1)
                                        <span class="badge bg-success">
                                            Active
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <td>

                                    <form action="{{ route('institute-image-settings.destroy', $item->id) }}"
                                        method="POST">

                                        <a href="{{ route('institute-image-settings.edit', $item->id) }}"
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

                        @empty

                            <tr>
                                <td colspan="8" class="text-center">
                                    No Data Found
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

                {{ $instituteImages->links() }}

            </div>

        </div>
    </div>
@endsection
