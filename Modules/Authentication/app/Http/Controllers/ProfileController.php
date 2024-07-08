<?php

namespace Modules\Authentication\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Services\DemoModeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => auth()->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $demoMode = app(DemoModeService::class);
        $demoMode->check('profile_change');

        $user = $request->user();

        $data = $request->validated();

        if (($data['email'] ?? $user->email) !== $user->email) {
            $demoMode->check('email_change');
        }

        // IMAGE UPLOAD
        if ($request->hasFile('image')) {

            // delete old image
            if (! empty($user->image) && file_exists(public_path($user->image))) {
                unlink(public_path($user->image));
            }

            $data['image'] = fileUploader(
                'users/',
                'png',
                $request->file('image')
            );
        }

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()
            ->route('profile.edit')
            ->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        app(DemoModeService::class)->check('account_delete');

        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
