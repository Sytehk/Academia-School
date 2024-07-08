<?php

namespace Modules\Authentication\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class LanguageController extends Controller
{
    public function index($lang): RedirectResponse
    {
        if ($lang != 'bn' && $lang != 'en') {
            abort(404);
        }
        app()->setLocale($lang);
        session()->put('locale', $lang);

        return back();
    }
}
