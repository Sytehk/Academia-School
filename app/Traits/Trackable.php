<?php

namespace App\Traits;

use Modules\SystemConfiguration\Models\UserLog;

trait Trackable
{
    public function trackAction($action = 'create', $model = null, $model_id = null, $detail = null): void
    {
        try {
            if (! auth()->check()) {
                return;
            }

            $user = auth()->user();
            $user_id = $user->id;
            $ip_address = request()->ip();
            $url = request()->fullUrl();

            $userLog = new UserLog;
            $userLog->user_id = $user_id;
            $userLog->institute_id = get_institute_id();
            $userLog->branch_id = get_branch_id();
            $userLog->user_id = $user_id;
            $userLog->ip_address = $ip_address;
            $userLog->action = $action;
            $userLog->detail = $detail;
            $userLog->model = $model;
            $userLog->url = $url;
            $userLog->model_id = $model_id;
            $userLog->save();
        } catch (\Throwable $th) {
            //
        }
    }
}
