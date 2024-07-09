@extends('layouts.backend')

@section('page_title_breadcrumb')
    Institute
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <div class="card">

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Institute Name</th>
                                    <th>Type</th>
                                    <th>Owner</th>
                                    <th>Phone</th>
                                    <th>Domain</th>
                                    <th>Package</th>
                                    <th>Subscription</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th width="180">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($institutes as $key => $institute)
                                    <tr>
                                        <td>
                                            {{ $institutes->firstItem() + $key }}
                                        </td>

                                        <td>
                                            {{ $institute->name }}
                                        </td>

                                        <td>
                                            {{ $institute->institute_type }}
                                        </td>

                                        <td>
                                            {{ $institute->owner->name ?? '-' }}
                                        </td>

                                        <td>
                                            {{ $institute->phone ?? '-' }}
                                        </td>

                                        <td>
                                            {{ $institute->domain ?? '-' }}
                                        </td>

                                        <td>
                                            {{ $institute->subscription->plan->name ?? 'No Package' }}
                                        </td>

                                        <td>
                                            @if ($institute->subscription)
                                                <span class="badge bg-success">
                                                    {{ ucfirst($institute->subscription->status) }}
                                                </span>
                                                <br>
                                                <small>
                                                    {{ $institute->subscription->start_date }}
                                                    -
                                                    {{ $institute->subscription->end_date }}
                                                </small>
                                            @else
                                                <span class="badge bg-secondary">
                                                    No Subscription
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            @if ($institute->status == 1)
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
                                            {{ $institute->created_at->format('d M Y') }}
                                        </td>

                                        <td>
                                            <!-- Subscription Update Button -->
                                            {{-- <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#subscriptionModal{{ $institute->id }}">
                                                Update Subscription
                                            </button> --}}

                                            <a href="{{ route('institutes.edit', $institute->id) }}"
                                                class="btn btn-sm btn-warning mt-2">
                                                Edit
                                            </a>

                                            <button type="button" class="btn btn-sm btn-danger mt-2"
                                                data-bs-toggle="modal" data-bs-target="#deleteInstitute{{ $institute->id }}">
                                                Delete
                                            </button>

                                            <button type="button" class="btn btn-sm btn-secondary mt-2"
                                                data-bs-toggle="modal" data-bs-target="#resetSettings{{ $institute->id }}">
                                                Reset Settings
                                            </button>

                                            <button type="button" class="btn btn-sm btn-outline-danger mt-2"
                                                data-bs-toggle="modal" data-bs-target="#factoryReset{{ $institute->id }}">
                                                Factory Reset
                                            </button>

                                            <!-- Delete Modal -->
                                            <div class="modal fade" id="deleteInstitute{{ $institute->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <form action="{{ route('institutes.destroy', $institute->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Delete Institute</h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Are you sure you want to delete
                                                                    <b>{{ $institute->name }}</b>? This action cannot
                                                                    be undone.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="submit" class="btn btn-danger">Confirm
                                                                    Delete</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

                                            <!-- Reset Settings Modal -->
                                            <div class="modal fade" id="resetSettings{{ $institute->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <form action="{{ route('institutes.reset-settings', $institute->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Reset Settings to Defaults</h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>This resets <b>{{ $institute->name }}</b>'s general
                                                                    settings, theme and home page content back to
                                                                    defaults. Students, classes, and other operational
                                                                    data are not affected.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="submit" class="btn btn-secondary">Reset
                                                                    Settings</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

                                            <!-- Factory Reset Modal -->
                                            <div class="modal fade" id="factoryReset{{ $institute->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <form action="{{ route('institutes.factory-reset', $institute->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title text-danger">Factory Reset —
                                                                    Destructive</h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p class="text-danger">This permanently wipes ALL
                                                                    operational data (students, classes, exams,
                                                                    invoices, attendance, etc.) for
                                                                    <b>{{ $institute->name }}</b> and reapplies
                                                                    default settings. This cannot be undone.</p>
                                                                <label class="form-label">Type
                                                                    <b>{{ $institute->name }}</b> to confirm:</label>
                                                                <input type="text" name="confirm_name"
                                                                    class="form-control" required>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="submit" class="btn btn-danger">I
                                                                    understand — Factory Reset</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

                                            <!-- Subscription Modal -->
                                            <div class="modal fade" id="subscriptionModal{{ $institute->id }}"
                                                tabindex="-1">
                                                <div class="modal-dialog">
                                                    <form
                                                        action="{{ route('institutes.subscription.update', $institute->id) }}"
                                                        method="POST">
                                                        @csrf

                                                        <div class="modal-content">

                                                            <div class="modal-header">
                                                                <h5 class="modal-title">
                                                                    Upgrade Subscription
                                                                </h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal">
                                                                </button>
                                                            </div>

                                                            <div class="modal-body">
                                                                {{-- Select Package --}}
                                                                <div class="mb-3">
                                                                    <label>
                                                                        Select Package
                                                                    </label>

                                                                    <select name="plan_id" class="form-control" required>

                                                                        <option value="">
                                                                            Select Plan
                                                                        </option>

                                                                        @foreach ($plans as $plan)
                                                                            <option value="{{ $plan->id }}">
                                                                                {{ $plan->name }}
                                                                                -
                                                                                {{ $plan->price }}
                                                                                ({{ $plan->duration_days }} Days)
                                                                            </option>
                                                                        @endforeach

                                                                    </select>
                                                                </div>

                                                                {{-- Start Date --}}
                                                                <div class="mb-3">

                                                                    <label>
                                                                        Start Date
                                                                    </label>

                                                                    <input type="date" name="start_date"
                                                                        class="form-control" value="{{ date('Y-m-d') }}"
                                                                        required>
                                                                </div>

                                                                {{-- Payment Amount --}}
                                                                <div class="mb-3">

                                                                    <label>
                                                                        Amount Paid
                                                                    </label>

                                                                    <input type="number" name="amount_paid"
                                                                        class="form-control" step="0.01" value="0">

                                                                </div>

                                                                {{-- Payment Method --}}
                                                                <div class="mb-3">

                                                                    <label>
                                                                        Payment Method
                                                                    </label>

                                                                    <select name="payment_method" class="form-control">

                                                                        <option value="manual">
                                                                            Manual
                                                                        </option>
                                                                        <option value="bank">
                                                                            Bank
                                                                        </option>
                                                                        <option value="cash">
                                                                            Cash
                                                                        </option>

                                                                    </select>

                                                                </div>
                                                            </div>

                                                            <div class="modal-footer">

                                                                <button class="btn btn-success">
                                                                    Upgrade Subscription
                                                                </button>

                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center">
                                            No institute found
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $institutes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
