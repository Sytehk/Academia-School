<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasPermissionTrait
{
    use ResponseTrait;

    public function checkPermission($permission)
    {
        if (Auth::user()->hasPermissionTo($permission, 'web')) {
            return true;
        }

        return $this->responseError([], _lang('You have no access on this feature'), 403);
    }

    public function handlePermission($permission)
    {
        return $this->checkPermission($permission);
    }
}
