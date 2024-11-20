@php
    // Self-resolving: no props required, so this can be dropped into any
    // admin page (`<x-system-update-status />`) without wiring data through
    // every controller. Renders nothing for users who can't act on it.
    // Toggle with SYSTEM_STATUS_WIDGET in .env; also hidden while healthy.
    $canManage = config('system.updates.dashboard_widget', true)
        && can_access_system('system.update.view');

    if ($canManage) {
        $status = app(\App\Services\SystemStatusService::class);

        $currentVersion = $status->currentVersion();
        $latestVersion = $status->latestVersion();
        $updateAvailable = $status->updateAvailable();
        $migrationsPending = $status->hasPendingMigrations();
        $storageLinked = $status->storageLinked();
        $needsAttention = $updateAvailable || $migrationsPending || ! $storageLinked;
        $canManage = $needsAttention;
    }
@endphp

@if ($canManage)

    <div class="sus-card {{ $needsAttention ? 'sus-card--attention' : '' }}">
        <div class="sus-head">
            <div class="sus-head-left">
                <span class="sus-icon"><i class="fa fa-shield-alt"></i></span>
                <div>
                    <div class="sus-title">{{ _lang('System') }}</div>
                    <div class="sus-subtitle">v{{ $currentVersion }}</div>
                </div>
            </div>

            @if (! $needsAttention)
                <span class="sus-pill sus-pill--ok">
                    <i class="fa fa-check-circle"></i> {{ _lang('Up to date') }}
                </span>
            @else
                <span class="sus-pill sus-pill--warn">
                    <i class="fa fa-exclamation-triangle"></i> {{ _lang('Action needed') }}
                </span>
            @endif
        </div>

        <ul class="sus-list">
            <li class="{{ $updateAvailable ? 'sus-item--warn' : 'sus-item--ok' }}">
                <i class="fa {{ $updateAvailable ? 'fa-arrow-circle-up' : 'fa-check' }}"></i>
                @if ($updateAvailable)
                    {{ _lang('Update available') }}: {{ $currentVersion }} &rarr; {{ $latestVersion }}
                @else
                    {{ _lang('Application up to date') }}
                @endif
            </li>

            <li class="{{ $migrationsPending ? 'sus-item--warn' : 'sus-item--ok' }}">
                <i class="fa {{ $migrationsPending ? 'fa-database' : 'fa-check' }}"></i>
                {{ $migrationsPending ? _lang('Database updates required') : _lang('Database up to date') }}
            </li>

            <li class="{{ $storageLinked ? 'sus-item--ok' : 'sus-item--warn' }}">
                <i class="fa {{ $storageLinked ? 'fa-check' : 'fa-link' }}"></i>
                {{ $storageLinked ? _lang('Storage linked') : _lang('Storage link missing') }}
            </li>

        </ul>

        @if ($needsAttention)
            <a href="{{ route('system.update.index') }}" class="sus-btn">
                {{ $updateAvailable || $migrationsPending ? _lang('Update Now') : _lang('Review System Status') }}
            </a>
        @else
            <a href="{{ route('system.update.index') }}" class="sus-link">{{ _lang('View system details') }}</a>
        @endif
    </div>

    <style>
        .sus-card {
            --sus-bg: #ffffff;
            --sus-bg-2: #f8fafc;
            --sus-text: #111827;
            --sus-muted: #6b7280;
            --sus-border: rgba(15, 23, 42, .08);
            --sus-ok: #059669;
            --sus-ok-bg: #ecfdf5;
            --sus-warn: #b45309;
            --sus-warn-bg: #fffbeb;
            --sus-accent: #2563eb;

            background: var(--sus-bg);
            border: 1px solid var(--sus-border);
            border-radius: 20px;
            padding: 20px 22px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
            margin-bottom: 24px;
        }

        html[data-bs-theme="dark"] .sus-card {
            --sus-bg: #111827;
            --sus-bg-2: #1e293b;
            --sus-text: #ffffff;
            --sus-muted: #94a3b8;
            --sus-border: rgba(255, 255, 255, .08);
            --sus-ok-bg: rgba(5, 150, 105, .15);
            --sus-warn-bg: rgba(180, 83, 9, .18);
        }

        .sus-card--attention {
            border-color: var(--sus-warn);
        }

        .sus-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .sus-head-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sus-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .sus-title {
            font-weight: 700;
            font-size: 15px;
            color: var(--sus-text);
        }

        .sus-subtitle {
            font-size: 12px;
            color: var(--sus-muted);
        }

        .sus-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 999px;
            white-space: nowrap;
        }

        .sus-pill--ok {
            color: var(--sus-ok);
            background: var(--sus-ok-bg);
        }

        .sus-pill--warn {
            color: var(--sus-warn);
            background: var(--sus-warn-bg);
        }

        .sus-list {
            list-style: none;
            margin: 0 0 16px;
            padding: 0;
            display: grid;
            gap: 8px;
        }

        .sus-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: var(--sus-text);
        }

        .sus-list li i {
            width: 16px;
            text-align: center;
            font-size: 12px;
        }

        .sus-item--ok i {
            color: var(--sus-ok);
        }

        .sus-item--warn i {
            color: var(--sus-warn);
        }

        .sus-btn {
            display: inline-block;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            color: #fff !important;
            font-weight: 600;
            font-size: 13px;
            padding: 9px 18px;
            border-radius: 10px;
            text-decoration: none;
            transition: opacity .2s ease;
        }

        .sus-btn:hover {
            opacity: .9;
        }

        .sus-link {
            font-size: 13px;
            font-weight: 600;
            color: var(--sus-accent);
            text-decoration: none;
        }

        .sus-link:hover {
            text-decoration: underline;
        }
    </style>
@endif
