<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\HasPermissionWebTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\GoogleMeet\Services\GoogleMeetService;
use RealRashid\SweetAlert\Facades\Alert;

class GoogleMeetSettingController extends Controller
{
    use HasPermissionWebTrait;

    public function __construct(private readonly GoogleMeetService $googleMeetService) {}

    public function connect(): bool|RedirectResponse
    {
        $permissionResult = $this->checkPermission('google_meet_setting_update');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        if (! get_option('google_meet_client_id') || ! get_option('google_meet_client_secret')) {
            Alert::error('Error', 'Please save the Client ID and Client Secret before connecting.');

            return redirect('administration/general_settings/update');
        }

        return redirect()->away($this->googleMeetService->getAuthorizationUrl());
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->has('error') || ! $request->has('code')) {
            Alert::error('Error', 'Google authorization was cancelled or failed.');

            return redirect('administration/general_settings/update');
        }

        $success = $this->googleMeetService->handleOAuthCallback($request->query('code'));

        if ($success) {
            Alert::success('Success', 'Google account connected successfully.');
        } else {
            Alert::error('Error', 'Failed to connect Google account. Please check your credentials.');
        }

        return redirect('administration/general_settings/update');
    }

    public function testConnection(): bool|RedirectResponse
    {
        $permissionResult = $this->checkPermission('google_meet_setting_update');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $result = $this->googleMeetService->testConnection();

        return redirect('administration/general_settings/update')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
