<?php

namespace Modules\Authentication\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\DemoModeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use RealRashid\SweetAlert\Facades\Alert;

class PasswordController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        app(DemoModeService::class)->check('password_change');
        $validated = $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],

            'password' => [
                'required',
                'confirmed',
            ],
        ]);

        $user = $request->user();

        // Update password
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Logout other devices
        Auth::logoutOtherDevices($validated['password']);

        // Keep current login
        Auth::login($user);

        $request->session()->regenerate();

        Alert::toast(
            'Password updated successfully. All other devices have been logged out.',
            'success'
        );

        return redirect()->route('profile.edit');
    }
}
