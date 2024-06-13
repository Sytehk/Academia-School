<?php

namespace App\Http\Middleware;

use App\Services\Installer\InstallationState;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstallationStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (InstallationState::isInstalled()) {
            return redirect('/'); // already installed
        }

        return $next($request);
    }
}
