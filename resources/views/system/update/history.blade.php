@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Update History') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">{{ _lang('Update History') }}</h3>
            <a href="{{ route('system.update.index') }}" class="btn btn-sm btn-light-primary">{{ _lang('System Update') }}</a>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-row-bordered align-middle">
                <thead>
                    <tr>
                        <th>{{ _lang('Version') }}</th>
                        <th>{{ _lang('Date') }}</th>
                        <th>{{ _lang('Type') }}</th>
                        <th>{{ _lang('Status') }}</th>
                        <th>{{ _lang('Admin') }}</th>
                        <th>{{ _lang('Duration') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($updates as $update)
                        <tr>
                            <td>{{ $update->from_version }} → {{ $update->to_version }}</td>
                            <td>{{ $update->created_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ ucfirst($update->type) }}</td>
                            <td><span class="badge badge-light">{{ $update->status }}</span></td>
                            <td>{{ $update->executor?->name ?? '—' }}</td>
                            <td>{{ $update->duration() ? $update->duration().'s' : '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('system.update.show', $update) }}" class="btn btn-sm btn-light">{{ _lang('View') }}</a>
                                @if (can_access_system('system.update.rollback'))
                                    @if ($update->backup_path && in_array($update->status, ['completed', 'failed']))
                                        <form method="POST" action="{{ route('system.update.rollback', $update) }}" class="d-inline"
                                            onsubmit="return confirm('{{ _lang('Rollback this update?') }}');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light-danger">{{ _lang('Rollback') }}</button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">{{ _lang('No update history yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $updates->links() }}
        </div>
    </div>
@endsection
