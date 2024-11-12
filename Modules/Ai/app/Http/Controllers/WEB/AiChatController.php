<?php

namespace Modules\Ai\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Ai\Models\AiConversation;
use Modules\Ai\Models\AiMessage;
use Modules\Ai\Services\ClaudeService;

class AiChatController extends Controller
{
    public function __construct(
        private readonly ClaudeService $claudeService
    ) {}

    public function index(): Factory|View
    {
        $conversations = AiConversation::where('user_id', Auth::id())
            ->where('feature', 'chat')
            ->latest()
            ->limit(20)
            ->get();

        $conversationId = request('conversation_id');
        $activeConversation = $conversationId
            ? AiConversation::where('user_id', Auth::id())->where('feature', 'chat')->find($conversationId)
            : null;

        $messages = $activeConversation ? $activeConversation->messages : collect();

        return view('ai::chat.index', [
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
            'messages' => $messages,
            'apiKeyConfigured' => ClaudeService::apiKeyConfigured(),
        ]);
    }

    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:8000',
            'conversation_id' => 'nullable|integer',
        ]);

        if (! ClaudeService::apiKeyConfigured()) {
            return response()->json([
                'status' => false,
                'message' => 'The Anthropic API key has not been configured yet. Ask an administrator to set it under AI Assistant > Settings.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $conversation = $request->conversation_id
                ? AiConversation::where('user_id', Auth::id())->where('feature', 'chat')->find($request->conversation_id)
                : null;

            if (! $conversation) {
                $conversation = AiConversation::create([
                    'institute_id' => get_institute_id(),
                    'branch_id' => get_branch_id(),
                    'user_id' => Auth::id(),
                    'feature' => 'chat',
                    'title' => str($request->message)->limit(60),
                ]);
            }

            AiMessage::create([
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'ai_conversation_id' => $conversation->id,
                'role' => 'user',
                'content' => $request->message,
            ]);

            // Full conversation history, oldest first, as the Claude API expects.
            $history = $conversation->messages()->orderBy('id')->get()
                ->map(fn (AiMessage $m) => ['role' => $m->role, 'content' => $m->content])
                ->all();

            $reply = $this->claudeService->chat($history);

            AiMessage::create([
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'ai_conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => $reply,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'conversation_id' => $conversation->id,
                'reply' => $reply,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
