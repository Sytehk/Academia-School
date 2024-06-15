<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

trait HasPermissionWebTrait
{
    public function checkPermission($permission)
    {
        if (Auth::user()->hasPermissionTo($permission, 'web')) {
            return true;
        }

        Alert::error('Error', _lang('You have no access on this feature'));

        return back();
    }

    public function handlePermission($permission)
    {
        return $this->checkPermission($permission);
    }
}
