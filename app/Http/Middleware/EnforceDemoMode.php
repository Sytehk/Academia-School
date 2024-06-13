<?php

namespace App\Http\Middleware;

use App\Services\DemoModeService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceDemoMode
{
    public function __construct(
        private readonly DemoModeService $demoMode,
    ) {}

    public function handle(Request $request, Closure $next, string $restriction): Response
    {
        $this->demoMode->check($restriction);

        return $next($request);
    }
}
