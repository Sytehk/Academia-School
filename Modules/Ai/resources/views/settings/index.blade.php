@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('AI Assistant Settings') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <p>
                        {{ _lang('The AI Assistant (Chat, Content Writer, Data Insights) is powered by the Anthropic Claude API. Enter your API key below to enable it.') }}
                    </p>

                    <div class="mb-3">
                        <span class="badge {{ $apiKeyConfigured ? 'bg-success' : 'bg-warning' }}">
                            {{ $apiKeyConfigured ? _lang('API Key Configured') : _lang('Not Configured') }}
                        </span>
                    </div>

                    <form action="{{ route('ai.settings.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">{{ _lang('Anthropic API Key') }}</label>
                            <input type="password" name="api_key" class="form-control"
                                placeholder="{{ $apiKeyConfigured ? _lang('•••••••••••••••• (leave blank to keep the current key)') : 'sk-ant-...' }}">
                        </div>
                        <button type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
                    </form>

                    @if ($apiKeyConfigured)
                        <form action="{{ route('ai.settings.test') }}" method="POST" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">{{ _lang('Test Connection') }}</button>
                        </form>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection
