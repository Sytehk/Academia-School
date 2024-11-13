@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('AI Chat Assistant') }}
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
                    <div id="ai-chat-messages" style="min-height: 300px; max-height: 55vh; overflow-y: auto; padding: 10px;">
                        @forelse ($messages as $message)
                            <div class="mb-3 {{ $message->role === 'user' ? 'text-end' : 'text-start' }}">
                                <div class="d-inline-block p-2 rounded {{ $message->role === 'user' ? 'bg-primary text-white' : 'bg-light' }}"
                                    style="max-width: 80%; white-space: pre-wrap;">{{ $message->content }}</div>
                            </div>
                        @empty
                            <p class="text-muted">{{ _lang('Start a conversation below.') }}</p>
                        @endforelse
                    </div>

                    <form id="ai-chat-form" class="d-flex gap-2 mt-3">
                        <input type="hidden" id="ai-chat-conversation-id" value="{{ $activeConversation->id ?? '' }}">
                        <textarea id="ai-chat-input" class="form-control" rows="2" placeholder="{{ _lang('Ask something...') }}" {{ $apiKeyConfigured ? '' : 'disabled' }}></textarea>
                        <button type="submit" class="btn btn-primary" {{ $apiKeyConfigured ? '' : 'disabled' }}>{{ _lang('Send') }}</button>
                    </form>
                </div>
            </div>

            @if ($conversations->isNotEmpty())
                <div class="card mt-3">
                    <div class="card-header">{{ _lang('Recent Conversations') }}</div>
                    <div class="list-group list-group-flush">
                        @foreach ($conversations as $conversation)
                            <a href="{{ route('ai.chat.index', ['conversation_id' => $conversation->id]) }}"
                                class="list-group-item list-group-item-action {{ ($activeConversation->id ?? null) === $conversation->id ? 'active' : '' }}">
                                {{ $conversation->title ?: _lang('Untitled') }}
                                <small class="text-muted float-end">{{ $conversation->created_at->format('d M Y') }}</small>
                            </a>
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
            const form = document.getElementById('ai-chat-form');
            const input = document.getElementById('ai-chat-input');
            const box = document.getElementById('ai-chat-messages');
            const convIdField = document.getElementById('ai-chat-conversation-id');
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            if (!form) return;

            function appendBubble(text, role) {
                const wrap = document.createElement('div');
                wrap.className = 'mb-3 ' + (role === 'user' ? 'text-end' : 'text-start');
                const bubble = document.createElement('div');
                bubble.className = 'd-inline-block p-2 rounded ' + (role === 'user' ? 'bg-primary text-white' : 'bg-light');
                bubble.style.maxWidth = '80%';
                bubble.style.whiteSpace = 'pre-wrap';
                bubble.textContent = text;
                wrap.appendChild(bubble);
                box.appendChild(wrap);
                box.scrollTop = box.scrollHeight;
            }

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const message = input.value.trim();
                if (!message) return;

                appendBubble(message, 'user');
                input.value = '';

                fetch('{{ route('ai.chat.send') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            message: message,
                            conversation_id: convIdField.value || null,
                        }),
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.status) {
                            convIdField.value = data.conversation_id;
                            appendBubble(data.reply, 'assistant');
                        } else {
                            appendBubble('Error: ' + data.message, 'assistant');
                        }
                    })
                    .catch(err => appendBubble('Error: ' + err.message, 'assistant'));
            });
        })();
    </script>
@endsection
