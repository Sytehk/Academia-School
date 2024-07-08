<?php

namespace Modules\Authentication\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Authentication\Http\Requests\Auth\RegisterRequest;
use Modules\Authentication\Http\Requests\ProfileUpdateRequest;
use Modules\Authentication\Models\User;

class AuthController extends Controller
{
    public function user(Request $request): JsonResponse
    {
        try {
            $user = User::where('id', Auth::id())->first();

            if ($user) {
                $permissions = $user->role?->permissions->pluck('name')->toArray() ?? []; // Convert permissions to an array

                $allowedKeys = [
                    'school_name',
                    'site_title',
                    'phone',
                    'email',
                    'language',
                    'google_map',
                    'address',
                    'timezone',
                    'academic_year',
                    'currency_symbol',
                    'logo',
                    'copyright_text',
                    'facebook_link',
                    'google_plus_link',
                    'youtube_link',
                    'whats_app_link',
                    'twitter_link',
                    'eiin_code',
                    'header_notice',
                    'exam_result_status',
                    'admission_display_status',
                    'guidance',
                    'academic_office',
                    'website_link',
                    'primary_color',
                    'secondary_color',
                    'primary_container_color',
                    'dark_primary_color',
                    'dark_secondary_color',
                    'dark_container_color',
                    'text_color',
                    'dark_text_color',
                    'sidebar_selected_bg_color',
                    'sidebar_selected_text_color',
                ];

                $settings = DB::table('settings')
                    ->whereIn('name', $allowedKeys)
                    ->where('institute_id', $user->institute_id)
                    ->pluck('value', 'name');

                $user = [
                    'id' => $user->id,
                    'institute_id' => $user->institute_id,
                    'branch_id' => $user->branch_id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'image' => $user->image,
                    'role' => $user->role?->name ?? 'no-role-assign',
                    'user_type' => $user->user_type,
                    'status' => $user->status,
                    'institute_info' => $user->institute,
                    'permissions' => $permissions,
                    'settings' => $settings,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'deleted_at' => $user->deleted_at,
                ];

                return $this->responseSuccess($user, 'Profile fetched successfully.');
            } else {
                return $this->responseError([], 'Profile not found.');
            }
        } catch (Exception $e) {
            return $this->responseError([], 'An error occurred while fetching the profile.');
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->responseError([], 'The provided credentials are incorrect..');
        }

        // A deactivated account cannot sign in, regardless of type.
        if ((int) ($user->status ?? 0) !== 1) {
            return $this->responseError([], _lang('Your account is inactive. Please contact the administrator.'), 403);
        }

        // A tenant user cannot sign in while their institute is deactivated.
        if ($user->user_type !== 'SAAS Admin') {
            $institute = $user->institute;
            if (! $institute || (int) $institute->status !== 1) {
                return $this->responseError([], _lang('This institute has been deactivated. Please contact the platform administrator.'), 403);
            }
        }

        $accessToken = $user->createToken('app')->accessToken;

        return $this->responseSuccess([
            'user' => $user,
            'access_token' => $accessToken,
        ]);
    }

    public function saasLogin(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->where('user_type', 'SAAS Admin')->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->responseError([], 'The provided credentials are incorrect..');
        }

        $accessToken = $user->createToken('app')->accessToken;

        return $this->responseSuccess([
            'user' => $user,
            'access_token' => $accessToken,
        ]);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        User::create([
            'name' => $request->name,
            'username' => Str::slug($request->name),
            'email' => $request->email,
            'phone' => $request->phone,
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make($request->password),

        ]);

        return $this->responseSuccess([], 'Registration Successfully Done.');
    }

    public function profileUpdate(ProfileUpdateRequest $request): JsonResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return $this->responseSuccess([], 'Profile Update Successfully Done.');
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return $this->responseSuccess([], 'Password Changes Successfully Done.');
    }

    public function logout(Request $request)
    {
        try {
            $result = $request->user()->token()->revoke();
            if ($result) {
                return $this->responseSuccess([], 'Logout Success');
            }
        } catch (Exception $e) {
            return $this->responseError([], 'Logout Failed');
        }
    }
}
