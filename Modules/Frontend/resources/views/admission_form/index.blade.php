@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Admission Applications') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            {{-- ALERTS --}}
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="table-responsive">

                <table class="table table-hover align-middle data-table">

                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Contact</th>
                            <th>Guardian</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th width="120">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($admissionForms as $item)
                            <tr>

                                {{-- ID --}}
                                <td>{{ $item->id }}</td>

                                {{-- STUDENT --}}
                                <td>
                                    <div class="d-flex align-items-center gap-2">

                                        @if ($item->image)
                                            <img src="{{ asset('storage/users/' . $item->image) }}" class="rounded-circle"
                                                width="40" height="40">
                                        @else
                                            <img src="{{ asset('images/no-image.png') }}" class="rounded-circle"
                                                width="40" height="40">
                                        @endif

                                        <div>
                                            <strong>
                                                {{ $item->first_name }} {{ $item->last_name }}
                                            </strong>

                                            <div class="text-muted small">
                                                {{ $item->email }}
                                            </div>
                                        </div>

                                    </div>
                                </td>

                                {{-- CLASS --}}
                                <td>
                                    <span class="badge bg-primary">
                                        {{ $item->class?->class_name ?? 'N/A' }}
                                    </span>
                                </td>

                                {{-- CONTACT --}}
                                <td>
                                    <div>
                                        📞 {{ $item->phone }}
                                    </div>
                                </td>

                                {{-- GUARDIAN --}}
                                <td>
                                    <strong>{{ $item->guardian_name }}</strong><br>
                                    <small class="text-muted">
                                        {{ $item->guardian_phone }}
                                    </small>
                                </td>

                                {{-- STATUS --}}
                                <td>
                                    @if ($item->status == 'Pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($item->status == 'Approved')
                                        <span class="badge bg-success">Approved</span>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>

                                {{-- DATE --}}
                                <td>
                                    {{ $item->created_at->format('d M Y') }}
                                </td>

                                <td>
                                    <div class="btn-group">
                                        {{-- VIEW --}}
                                        {{-- <a href="{{ route('admission-forms.show', $item->id) }}"
                                            class="btn btn-sm btn-info">
                                            View
                                        </a> --}}

                                        {{-- APPROVE --}}
                                        @if ($item->status == 'Pending')
                                            <form action="{{ route('admission-forms.approve', $item->id) }}"
                                                method="POST">
                                                @csrf

                                                <button type="submit" class="btn btn-sm btn-success"
                                                    onclick="return confirm('Approve this admission?')">
                                                    Approve
                                                </button>
                                            </form>

                                            {{-- REJECT --}}
                                            <form action="{{ route('admission-forms.reject', $item->id) }}"
                                                method="POST">
                                                @csrf

                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Reject this admission?')">
                                                    Reject
                                                </button>
                                            </form>
                                        @endif

                                        {{-- DELETE --}}
                                        <form action="{{ route('admission-forms.destroy', $item->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">
                                                Delete
                                            </button>
                                        </form>

                                    </div>
                                </td>

                            </tr>
                        @endforeach

                    </tbody>

                </table>

            </div>

            {{-- PAGINATION --}}
            <div class="mt-3">
                {{ $admissionForms->links() }}
            </div>

        </div>
    </div>
@endsection
