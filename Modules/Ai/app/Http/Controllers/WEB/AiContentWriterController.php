<?php

namespace Modules\Ai\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Ai\Models\AiConversation;
use Modules\Ai\Models\AiMessage;
use Modules\Ai\Services\ClaudeService;

class AiContentWriterController extends Controller
{
    public const CONTENT_TYPES = [
        'notice' => 'Notice / Announcement',
        'report_remark' => 'Report Card Remark',
        'letter' => 'Formal Letter',
        'circular' => 'Circular',
        'other' => 'Other',
    ];

    public function __construct(
        private readonly ClaudeService $claudeService
    ) {}

    public function index(): Factory|View
    {
        $history = AiConversation::where('user_id', Auth::id())
            ->where('feature', 'writer')
            ->latest()
            ->limit(10)
            ->get();

        return view('ai::writer.index', [
            'contentTypes' => self::CONTENT_TYPES,
            'history' => $history,
            'apiKeyConfigured' => ClaudeService::apiKeyConfigured(),
        ]);
    }

    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'content_type' => 'required|string|in:'.implode(',', array_keys(self::CONTENT_TYPES)),
            'prompt' => 'required|string|max:4000',
        ]);

        if (! ClaudeService::apiKeyConfigured()) {
            return response()->json([
                'status' => false,
                'message' => 'The Anthropic API key has not been configured yet. Ask an administrator to set it under AI Assistant > Settings.',
            ], 422);
        }

        try {
            $contentTypeLabel = self::CONTENT_TYPES[$request->content_type];
            $result = $this->claudeService->generateContent($request->prompt, $contentTypeLabel);

            $conversation = AiConversation::create([
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'user_id' => Auth::id(),
                'feature' => 'writer',
                'title' => $contentTypeLabel.': '.str($request->prompt)->limit(50),
            ]);

            AiMessage::create([
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'ai_conversation_id' => $conversation->id,
                'role' => 'user',
                'content' => $request->prompt,
            ]);

            AiMessage::create([
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'ai_conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => $result,
            ]);

            return response()->json([
                'status' => true,
                'result' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
