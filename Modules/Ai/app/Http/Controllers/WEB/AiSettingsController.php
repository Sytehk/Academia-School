<?php

namespace Modules\Ai\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Ai\Services\ClaudeService;
use RealRashid\SweetAlert\Facades\Alert;

class AiSettingsController extends Controller
{
    public function __construct(
        private readonly ClaudeService $claudeService
    ) {}

    public function index(): Factory|View
    {
        return view('ai::settings.index', [
            'apiKeyConfigured' => ClaudeService::apiKeyConfigured(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'api_key' => 'nullable|string|max:255',
        ]);

        // An empty submitted value with a key already configured means "leave it
        // unchanged" — the settings form never displays the stored key back to the
        // browser, so a blank submit is not the same thing as "clear the key".
        if ($request->filled('api_key')) {
            ClaudeService::saveApiKey($request->api_key);
            Alert::toast('AI Assistant API key saved.', 'success');
        }

        return back();
    }

    public function testConnection(): RedirectResponse
    {
        $result = $this->claudeService->testConnection();

        if ($result['success']) {
            Alert::success('Success', $result['message']);
        } else {
            Alert::error('Connection failed', $result['message']);
        }

        return back();
    }
}
