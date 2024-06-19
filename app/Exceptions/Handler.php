<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // Get the login type from the request.
        $loginType = request()->query->all()['type'] ?? 'admin';
        session()->put('login_type', $loginType);

        return parent::unauthenticated($request, $exception);
    }

    public function render($request, Throwable $exception)
    {
        return parent::render($request, $exception);
    }
}
