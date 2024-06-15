<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait RequestSanitizerTrait
{
    protected function getUpdateRequest(Request $request)
    {
        return $request->except(['_method', 'id']);
    }
}
