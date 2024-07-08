<?php

namespace Modules\Authentication\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Authentication\Models\DomainRequest;
use Modules\Authentication\Models\Institute;
use RealRashid\SweetAlert\Facades\Alert;

class DomainRequestController extends Controller
{
    public function index(): Factory|View
    {
        $institute = Institute::find(get_institute_id());

        $requests = DomainRequest::where('institute_id', get_institute_id())
            ->latest()
            ->get();

        return view('authentication::domain.index', [
            'institute' => $institute,
            'requests' => $requests,
            'serverIp' => $this->resolveServerIp(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'requested_domain' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $hasPending = DomainRequest::where('institute_id', get_institute_id())
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            Alert::error('Error', _lang('You already have a pending domain request. Please wait for it to be reviewed.'));

            return redirect()->route('custom-domain.index');
        }

        DomainRequest::create([
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'requested_domain' => strtolower($request->input('requested_domain')),
            'status' => 'pending',
            'note' => $request->input('note'),
            'requested_by' => Auth::id(),
        ]);

        Alert::toast(_lang('Domain request submitted. It will be reviewed by the platform administrator.'), 'success');

        return redirect()->route('custom-domain.index');
    }

    private function resolveServerIp(): ?string
    {
        $configured = config('services.platform.server_ip');

        if (! blank($configured)) {
            return $configured;
        }

        $host = parse_url(config('app.url'), PHP_URL_HOST);

        if (! $host) {
            return null;
        }

        $ip = @gethostbyname($host);

        return ($ip && $ip !== $host) ? $ip : null;
    }
}
