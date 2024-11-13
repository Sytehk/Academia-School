@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('AI Content Writer') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">

            @unless ($apiKeyConfigured)
                <div class="alert alert-warning">
                    {{ _lang('The Anthropic API key has not been configured yet.') }}
                    <a href="{{ route('ai.settings.index') }}">{{ _lang('Configure it here') }}</a>.
                </div>
            @endunless

            <div class="card">
                <div class="card-body">
                    <form id="ai-writer-form">
                        <div class="mb-3">
                            <label class="form-label">{{ _lang('Content Type') }}</label>
                            <select name="content_type" id="ai-writer-type" class="form-select" required>
                                @foreach ($contentTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ _lang('What should it say?') }}</label>
                            <textarea name="prompt" id="ai-writer-prompt" class="form-control" rows="4" required
                                placeholder="{{ _lang('e.g. A notice informing parents that the school will be closed on Thursday for a public holiday.') }}"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" {{ $apiKeyConfigured ? '' : 'disabled' }}>{{ _lang('Generate') }}</button>
                    </form>

                    <div id="ai-writer-result-wrap" class="mt-4" style="display:none;">
                        <label class="form-label">{{ _lang('Result') }}</label>
                        <textarea id="ai-writer-result" class="form-control" rows="10" readonly></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if ($history->isNotEmpty())
                <div class="card">
                    <div class="card-header">{{ _lang('Recent Generations') }}</div>
                    <div class="list-group list-group-flush">
                        @foreach ($history as $item)
                            <div class="list-group-item">
                                {{ $item->title }}
                                <br><small class="text-muted">{{ $item->created_at->format('d M Y, H:i') }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function() {
            const form = document.getElementById('ai-writer-form');
            const resultWrap = document.getElementById('ai-writer-result-wrap');
            const resultBox = document.getElementById('ai-writer-result');
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.textContent = '{{ _lang('Generating...') }}';

                fetch('{{ route('ai.writer.generate') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            content_type: document.getElementById('ai-writer-type').value,
                            prompt: document.getElementById('ai-writer-prompt').value,
                        }),
                    })
                    .then(r => r.json())
                    .then(data => {
                        resultWrap.style.display = 'block';
                        resultBox.value = data.status ? data.result : ('Error: ' + data.message);
                    })
                    .catch(err => {
                        resultWrap.style.display = 'block';
                        resultBox.value = 'Error: ' + err.message;
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.textContent = '{{ _lang('Generate') }}';
                    });
            });
        })();
    </script>
@endsection
