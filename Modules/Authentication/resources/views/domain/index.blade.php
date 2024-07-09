@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Custom Domain') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-default mb-4">
                <div class="card-header">
                    <div class="card-title">{{ _lang('Current Domain') }}</div>
                </div>
                <div class="card-body">
                    <p class="mb-2"><b>{{ _lang('Active domain') }}:</b> {{ $institute->domain ?? _lang('Not set') }}</p>

                    @if ($serverIp)
                        <p class="mb-2"><b>{{ _lang('Server IP address') }}:</b> <code>{{ $serverIp }}</code></p>
                    @else
                        <p class="mb-2 text-muted">{{ _lang('Server IP address could not be resolved automatically. Contact your platform administrator.') }}</p>
                    @endif

                    <hr>

                    <p class="mb-1"><b>{{ _lang('How to point your domain (Cloudflare)') }}</b></p>
                    <ol class="ps-3 mb-0">
                        <li>{{ _lang('Log in to Cloudflare and select your domain.') }}</li>
                        <li>{{ _lang('Go to DNS > Records.') }}</li>
                        @if ($serverIp)
                            <li>{{ _lang('Add an A record: Name = @ (or your subdomain), IPv4 address =') }} <code>{{ $serverIp }}</code></li>
                        @else
                            <li>{{ _lang('Add an A record pointing to the server IP address provided by your platform administrator.') }}</li>
                        @endif
                        <li>{{ _lang('Set the Proxy status to DNS only while the request is pending review.') }}</li>
                        <li>{{ _lang('Submit your domain below. The platform administrator will review and approve it.') }}</li>
                    </ol>
                </div>
            </div>

            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">{{ _lang('Request a Domain Change') }}</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('custom-domain.store') }}" method="post">
                        @csrf
                        <div class="form-group mb-3">
                            <label class="form-label">{{ _lang('Domain') }}</label>
                            <input type="text" class="form-control" name="requested_domain"
                                value="{{ old('requested_domain') }}" placeholder="school.example.com" required>
                            @error('requested_domain')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">{{ _lang('Note (optional)') }}</label>
                            <textarea class="form-control" name="note" rows="2">{{ old('note') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ _lang('Submit Request') }}</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">{{ _lang('Request History') }}</div>
                </div>
                <div class="">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                            <th>{{ _lang('Domain') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Submitted') }}</th>
                        </thead>
                        <tbody>
                            @forelse ($requests as $domainRequest)
                                <tr>
                                    <td>{{ $domainRequest->requested_domain }}</td>
                                    <td>
                                        @if ($domainRequest->status === 'approved')
                                            <span class="badge bg-success">{{ _lang('Approved') }}</span>
                                        @elseif ($domainRequest->status === 'rejected')
                                            <span class="badge bg-danger">{{ _lang('Rejected') }}</span>
                                        @else
                                            <span class="badge bg-warning">{{ _lang('Pending') }}</span>
                                        @endif
                                        @if ($domainRequest->review_note)
                                            <div class="text-muted small mt-1">{{ $domainRequest->review_note }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $domainRequest->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-danger">{{ _lang('No requests yet') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
