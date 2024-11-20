@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Package Validation') }}
@endsection

@section('content')
    <div class="card mb-5">
        <div class="card-header">
            <h3 class="card-title">{{ _lang('Package Validation') }} — {{ $packageName }}</h3>
        </div>
        <div class="card-body">
            <h4 class="mb-4">{{ _lang('Validation Results') }}</h4>

            <ul class="list-unstyled mb-6">
                @foreach ($validation['checks'] as $key => $check)
                    <li class="mb-2">
                        @if ($check['passed'])
                            <span class="text-success">✓</span>
                        @else
                            <span class="text-danger">✕</span>
                        @endif
                        <strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong>
                        {{ $check['message'] }}
                    </li>
                @endforeach
            </ul>

            @if ($validation['ready'])
                <div class="alert alert-success">{{ _lang('Ready to install.') }}</div>

                <form method="POST" action="{{ route('system.update.install') }}"
                    onsubmit="return confirm('{{ _lang('You are about to update. A backup will be created. Continue?') }}');">
                    @csrf
                    <button type="submit" class="btn btn-success">{{ _lang('Install Update') }}</button>
                    <a href="{{ route('system.update.index') }}" class="btn btn-light">{{ _lang('Cancel') }}</a>
                </form>
            @else
                <div class="alert alert-danger">{{ _lang('Installation is not allowed until all checks pass.') }}</div>
                <a href="{{ route('system.update.index') }}" class="btn btn-light">{{ _lang('Back') }}</a>
            @endif
        </div>
    </div>
@endsection
