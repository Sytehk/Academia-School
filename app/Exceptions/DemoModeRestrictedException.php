<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class DemoModeRestrictedException extends Exception
{
    public function __construct(
        public readonly string $restrictionKey = '',
        string $message = '',
        public readonly int $statusCode = 403,
    ) {
        parent::__construct($message ?: (string) config('system.demo.message', 'This action is disabled in demo mode.'));
    }

    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->ajax() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'restriction' => $this->restrictionKey,
            ], $this->statusCode);
        }

        try {
            Alert::toast($this->getMessage(), 'error');
        } catch (\Throwable) {
            // Session may be unavailable outside a web request.
        }

        return back()
            ->with('error', $this->getMessage())
            ->with('demo_restricted', $this->restrictionKey);
    }
}
