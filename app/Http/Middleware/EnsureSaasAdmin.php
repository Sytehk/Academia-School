<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for the platform-administration API (institute CRUD, plans, onboarding,
 * subscription upgrades, SaaS CMS/settings, ...).
 *
 * `auth:api` alone only proves the caller holds a valid token — and every
 * institute user (Admin/Teacher/Student) is issued one at /api/login. Without
 * this check any of them could list or mutate every tenant on the platform.
 * The SaaS panel authenticates through /api/saas-login, which only ever mints
 * a token for a `SAAS Admin`, so this middleware is transparent to it.
 */
class EnsureSaasAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->user_type !== 'SAAS Admin') {
            return response()->json([
                'status' => false,
                'message' => 'This action is restricted to platform administrators.',
                'data' => [],
            ], 403);
        }

        return $next($request);
    }
}
