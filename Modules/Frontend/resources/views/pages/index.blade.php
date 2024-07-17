@extends('layouts.backend')

@section('create-button')
    <a href="{{ route('pages.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add Page') }}
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th>Title</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                @foreach ($pages as $page)
                    <tr>
                        <td>{{ $page->title }}</td>
                        <td>{{ $page->slug }}</td>
                        <td>{{ $page->page_status }}</td>
                        <td>

                            <form action="{{ route('pages.destroy', $page->id) }}" method="POST">
                                <a href="{{ route('pages.edit', $page->id) }}" class="btn btn-warning btn-sm">Edit</a>

                                @csrf
                                @method('DELETE')

                                <button class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection
