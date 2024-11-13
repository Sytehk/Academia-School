@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('AI Data Insights') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            @unless ($apiKeyConfigured)
                <div class="alert alert-warning">
                    {{ _lang('The Anthropic API key has not been configured yet.') }}
                    <a href="{{ route('ai.settings.index') }}">{{ _lang('Configure it here') }}</a>.
                </div>
            @endunless

            <div class="card">
                <div class="card-body">
                    <form id="ai-insights-form" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">{{ _lang('Report') }}</label>
                            <select name="report_type" id="ai-insights-report-type" class="form-select" required>
                                @foreach ($reportTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ _lang('Class (optional)') }}</label>
                            <select name="class_id" id="ai-insights-class" class="form-select">
                                <option value="">{{ _lang('All Classes') }}</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ _lang('From') }}</label>
                            <input type="date" name="from_date" id="ai-insights-from" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ _lang('To') }}</label>
                            <input type="date" name="to_date" id="ai-insights-to" class="form-control">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100" {{ $apiKeyConfigured ? '' : 'disabled' }}>{{ _lang('Summarize') }}</button>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ _lang('Ask a specific question (optional)') }}</label>
                            <input type="text" name="question" id="ai-insights-question" class="form-control"
                                placeholder="{{ _lang('e.g. Which class has the lowest attendance this month?') }}">
                        </div>
                    </form>

                    <div id="ai-insights-result-wrap" class="mt-4" style="display:none;">
                        <label class="form-label">{{ _lang('Summary') }}</label>
                        <div id="ai-insights-summary" class="p-3 bg-light rounded" style="white-space: pre-wrap;"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function() {
            const form = document.getElementById('ai-insights-form');
            const resultWrap = document.getElementById('ai-insights-result-wrap');
            const summaryBox = document.getElementById('ai-insights-summary');
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.textContent = '{{ _lang('Summarizing...') }}';

                fetch('{{ route('ai.insights.generate') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            report_type: document.getElementById('ai-insights-report-type').value,
                            class_id: document.getElementById('ai-insights-class').value || null,
                            from_date: document.getElementById('ai-insights-from').value || null,
                            to_date: document.getElementById('ai-insights-to').value || null,
                            question: document.getElementById('ai-insights-question').value || null,
                        }),
                    })
                    .then(r => r.json())
                    .then(data => {
                        resultWrap.style.display = 'block';
                        summaryBox.textContent = data.status ? data.summary : ('Error: ' + data.message);
                    })
                    .catch(err => {
                        resultWrap.style.display = 'block';
                        summaryBox.textContent = 'Error: ' + err.message;
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.textContent = '{{ _lang('Summarize') }}';
                    });
            });
        })();
    </script>
@endsection
