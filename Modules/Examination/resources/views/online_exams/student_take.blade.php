@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ $attempt->onlineExam->name }}
@endsection
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">{{ $attempt->onlineExam->name }}</h3>
            <div class="fw-bold text-danger" id="exam-timer">--:--</div>
        </div>
        <div class="card-body">
            <form id="exam-form" method="POST" action="{{ route('student.online-exams.submit', $attempt->id) }}">
                @csrf
                @foreach ($questions as $index => $question)
                    <div class="border rounded p-4 mb-4 question-card" data-question-id="{{ $question['id'] }}">
                        <h5>{{ $index + 1 }}. {!! $question['question_text'] !!} <small>({{ $question['marks'] }})</small></h5>
                        @if ($question['image'])
                            <img src="{{ $question['image'] }}" alt="" class="img-fluid mb-3" style="max-height:180px">
                        @endif
                        @php $saved = collect($attempt->answers ?? [])->firstWhere('question_id', $question['id']); $selected = $saved['selected_options'] ?? $saved['answer'] ?? []; @endphp
                        @if (in_array($question['type'], ['multiple_choice', 'true_false']))
                            @foreach ($question['options'] as $optionKey => $option)
                                <label class="d-block mb-2">
                                    <input type="radio" name="answers[{{ $index }}][selected_options][]" value="{{ is_int($optionKey) ? $option : $optionKey }}"
                                        @checked(in_array((string) (is_int($optionKey) ? $option : $optionKey), array_map('strval', (array) $selected), true))>
                                    {{ $option }}
                                </label>
                            @endforeach
                        @elseif ($question['type'] === 'multiple_select')
                            @foreach ($question['options'] as $optionKey => $option)
                                <label class="d-block mb-2">
                                    <input type="checkbox" name="answers[{{ $index }}][selected_options][]" value="{{ is_int($optionKey) ? $option : $optionKey }}"
                                        @checked(in_array((string) (is_int($optionKey) ? $option : $optionKey), array_map('strval', (array) $selected), true))>
                                    {{ $option }}
                                </label>
                            @endforeach
                        @else
                            <textarea class="form-control" name="answers[{{ $index }}][answer]">{{ is_array($selected) ? implode(' ', $selected) : $selected }}</textarea>
                        @endif
                        <input type="hidden" name="answers[{{ $index }}][question_id]" value="{{ $question['id'] }}">
                    </div>
                @endforeach
                <button class="btn btn-primary" type="submit">{{ _lang('Submit Exam') }}</button>
            </form>
        </div>
    </div>
@endsection
@section('js-script')
    <script>
        (function() {
            const expiresAt = new Date(@json(optional($attempt->expires_at)->toIso8601String()));
            const form = document.getElementById('exam-form');
            const timerEl = document.getElementById('exam-timer');
            const saveUrl = @json(route('student.online-exams.save', $attempt->id));
            const token = document.querySelector('input[name=_token]').value;

            function remaining() {
                const diff = Math.max(0, Math.floor((expiresAt.getTime() - Date.now()) / 1000));
                const m = String(Math.floor(diff / 60)).padStart(2, '0');
                const s = String(diff % 60).padStart(2, '0');
                timerEl.textContent = m + ':' + s;
                if (diff <= 0) {
                    form.submit();
                }
            }
            remaining();
            setInterval(remaining, 1000);

            function collect() {
                return new FormData(form);
            }
            async function autosave() {
                const body = new URLSearchParams(collect());
                body.append('_token', token);
                try {
                    await fetch(saveUrl, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body });
                } catch (e) {}
            }
            setInterval(autosave, 15000);
            window.addEventListener('beforeunload', function() { navigator.sendBeacon(saveUrl, collect()); });
        })();
    </script>
@stop
