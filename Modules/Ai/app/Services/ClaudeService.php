<?php

namespace Modules\Ai\Services;

use Anthropic\Client;
use Modules\SystemConfiguration\Models\Setting;
use RuntimeException;
use Throwable;

/**
 * Thin wrapper around the Anthropic Claude API (anthropic-ai/sdk) for the AI Assistant
 * module's three features: chat, content writing, and data-insights summarization.
 *
 * The API key is a tenant-scoped setting (get_option()/Setting::updateOrCreate — the
 * same pattern every other third-party integration in this codebase uses, e.g.
 * GoogleMeetService), configured via the module's Settings page.
 */
class ClaudeService
{
    /**
     * Free-form chat: full message history in, assistant reply text out.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function chat(array $messages, ?string $systemPrompt = null): string
    {
        return $this->send($messages, $systemPrompt);
    }

    /**
     * One-shot content generation (reports, notices, remarks, letters, ...).
     */
    public function generateContent(string $prompt, ?string $contentType = null): string
    {
        $system = 'You write clear, professional, ready-to-use written content for a school '
            .'administration system (notices, report-card remarks, letters, announcements). '
            .'Write only the requested content — no preamble, no meta-commentary about the task.';

        if ($contentType) {
            $system .= " The requested content type is: {$contentType}.";
        }

        return $this->send([['role' => 'user', 'content' => $prompt]], $system);
    }

    /**
     * Summarize a pre-aggregated data snapshot (built by the caller from existing report
     * queries — this method never queries the database itself) into a plain-language answer.
     */
    public function summarize(string $dataContext, string $question): string
    {
        $system = 'You are a data-insights assistant for a school management system. Base your '
            .'answer strictly on the data provided below — do not invent figures that are not '
            .'present in it. Answer in clear, concise plain language suitable for a school '
            .'administrator, not a data scientist.';

        $prompt = "Data:\n{$dataContext}\n\nQuestion: {$question}";

        return $this->send([['role' => 'user', 'content' => $prompt]], $system);
    }

    /**
     * Cheap round-trip used by the Settings page's "Test Connection" button.
     */
    public function testConnection(): array
    {
        try {
            $this->send([['role' => 'user', 'content' => 'Reply with exactly the word OK.']], null);

            return ['success' => true, 'message' => 'Connected to the Claude API successfully.'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function apiKeyConfigured(): bool
    {
        return ! empty(get_option('ai_anthropic_api_key'));
    }

    public static function saveApiKey(?string $apiKey): void
    {
        Setting::updateOrCreate(
            [
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'name' => 'ai_anthropic_api_key',
            ],
            ['type' => 'general', 'value' => $apiKey]
        );
    }

    /**
     * Core call. Isolated in its own method so it can be swapped out in tests without
     * hitting the real API — see the module's verification script.
     */
    protected function send(array $messages, ?string $systemPrompt): string
    {
        $apiKey = get_option('ai_anthropic_api_key');

        if (empty($apiKey)) {
            throw new RuntimeException('Anthropic API key is not configured. Set it under AI Assistant > Settings.');
        }

        $client = new Client(apiKey: $apiKey);

        $params = [
            'model' => config('ai.model', 'claude-opus-5'),
            'maxTokens' => (int) config('ai.max_tokens', 4096),
            'messages' => $messages,
            'thinking' => ['type' => 'adaptive'],
            'outputConfig' => ['effort' => config('ai.effort', 'medium')],
        ];

        if ($systemPrompt) {
            $params['system'] = $systemPrompt;
        }

        $message = $client->messages->create(...$params);

        return $this->extractText($message);
    }

    private function extractText(object $message): string
    {
        $text = '';
        foreach ($message->content as $block) {
            if ($block->type === 'text') {
                $text .= $block->text;
            }
        }

        return $text;
    }
}
