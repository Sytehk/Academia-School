@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Modules') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ _lang('Module Management') }}</h3>
        </div>
        <div class="card-body table-responsive">
            @if ($demoMode)
                <div class="alert alert-warning">
                    {{ _lang('Modules cannot be disabled while demo mode is enabled.') }}
                </div>
            @endif
            <table class="table table-row-bordered align-middle">
                <thead>
                    <tr>
                        <th>{{ _lang('Module') }}</th>
                        <th>{{ _lang('Version') }}</th>
                        <th>{{ _lang('Status') }}</th>
                        <th>{{ _lang('Dependencies') }}</th>
                        <th class="text-end">{{ _lang('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($modules as $module)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $module->name }}</div>
                                <div class="text-muted small">{{ $module->description }}</div>
                            </td>
                            <td>{{ $module->version }}</td>
                            <td>
                                <span class="badge {{ $module->enabled ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $module->enabled ? _lang('Enabled') : _lang('Disabled') }}
                                </span>
                            </td>
                            <td>{{ implode(', ', $module->dependencies ?? []) ?: '—' }}</td>
                            <td class="text-end">
                                @if ($module->enabled)
                                    @if (can_access_system('system.module.disable'))
                                        <form method="POST" action="{{ route('system.modules.disable', $module->slug) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light-warning" @disabled($demoMode)>
                                                {{ _lang('Disable') }}
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    @if (can_access_system('system.module.enable'))
                                        <form method="POST" action="{{ route('system.modules.enable', $module->slug) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light-success">
                                                {{ _lang('Enable') }}
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
