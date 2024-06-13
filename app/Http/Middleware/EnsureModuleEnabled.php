<?php

namespace App\Http\Middleware;

use App\Services\ModuleManagerService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    public function __construct(
        private readonly ModuleManagerService $modules,
    ) {}

    public function handle(Request $request, Closure $next, string $moduleSlug): Response
    {
        if (! $this->modules->isEnabled($moduleSlug)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This module is currently disabled.',
                ], 403);
            }

            abort(403, 'This module is currently disabled.');
        }

        return $next($request);
    }
}
