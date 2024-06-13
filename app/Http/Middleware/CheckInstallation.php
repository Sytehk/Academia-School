<?php

namespace App\Http\Middleware;

use App\Services\Installer\InstallationState;
use Closure;

class CheckInstallation
{
    public function handle($request, Closure $next)
    {
        if (! InstallationState::isInstalled()) {
            return redirect('/install');
        }

        return $next($request);
    }
}
