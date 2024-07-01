@extends('layouts.backend')

@section('page_title_breadcrumb')
    Zoom Meetings
@endsection

@section('create-button')
    <a href="{{ route('zoom.create') }}" class="btn btn-primary">
        Create Meeting
    </a>
@endsection

@section('content')
    @if (!$zoomConnected)
        <div class="alert alert-danger">
            Zoom API Connection Failed
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">

                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Topic</th>
                            <th>Teacher</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Meeting ID</th>
                            <th>Date</th>
                            <th>Duration</th>
                            <th>Join URL</th>
                            <th>Created By</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($meetings as $key=>$meeting)
                            <tr>

                                <td>{{ $key + 1 }}</td>

                                <td>
                                    {{ $meeting->topic }}
                                    @if ($meeting->agenda)
                                        <br>
                                        <small>{{ Str::limit($meeting->agenda, 40) }}</small>
                                    @endif
                                </td>

                                <td>{{ $meeting->teacher?->name ?? 'N/A' }}</td>

                                <td>{{ $meeting->class?->class_name ?? 'N/A' }}</td>

                                <td>{{ $meeting->section?->section_name ?? 'N/A' }}</td>

                                <td>
                                    <span class="badge bg-info">
                                        {{ $meeting->meeting_id }}
                                    </span>
                                </td>

                                <td>
                                    {{ date('d M Y h:i A', strtotime($meeting->start_time)) }}
                                </td>

                                <td>
                                    {{ $meeting->duration }} Min
                                </td>

                                <td>
                                    @if ($meeting->join_url)
                                        <a href="{{ $meeting->join_url }}" target="_blank" class="btn btn-sm btn-success">
                                            Join
                                        </a>
                                    @endif
                                </td>

                                <td>
                                    {{ $meeting->createdBy?->name ?? 'N/A' }}
                                </td>

                                <td>
                                    <div class="d-flex gap-1">

                                        <a href="{{ route('zoom.edit', $meeting->id) }}" class="btn btn-sm btn-warning">
                                            Edit
                                        </a>

                                        <form action="{{ route('zoom.destroy', $meeting->id) }}" method="POST">

                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-sm btn-danger"
                                                onclick="return confirm('Delete meeting?')">
                                                Delete
                                            </button>

                                        </form>

                                    </div>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="11" class="text-center">
                                    No Meeting Found
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>
        </div>
    </div>
@endsection
