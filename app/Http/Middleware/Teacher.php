<?php

namespace App\Http\Middleware;

use App\Traits\ResponseTrait;
use Closure;
use Illuminate\Http\Request;

class Teacher
{
    use ResponseTrait;

    public function handle(Request $request, Closure $next)
    {
        return PortalGate::check($request, $next, 'Teacher', $this);
    }
}
