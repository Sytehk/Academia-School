<?php

namespace Modules\Student\Http\Controllers\API;

use App\Helpers\InstituteHelper;
use App\Http\Controllers\Controller;
use App\Traits\Authenticatable;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Authentication\Models\User;
use Modules\Student\Http\Requests\Auth\StudentLoginRequest;
use Modules\Student\Http\Requests\Auth\StudentOtpLoginRequest;
use Modules\Student\Models\OtpVerification;

class StudentAuthController extends Controller
{
    use Authenticatable;

    public function studentProfile(Request $request)
    {
        try {
            $user = User::with([
                'student.deviceControl',
                'student.devices',
                'student.studentSession.class',
                'student.studentSession.section',
            ])->find(Auth::id());

            if ($user) {
                $student = $user->student;
                $session = $student?->studentSession;

                $payload = [
                    'id' => $user->id,
                    'name' => $user->name
                        ?: trim(($student->first_name ?? '').' '.($student->last_name ?? '')),
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'image' => $user->image,
                    'status' => $user->status,
                    'student_id' => $student?->id,
                    'student_device_control' => $student?->deviceControl,
                    'student_devices' => $student?->devices ?? [],
                    'class_id' => $session?->class_id,
                    'class_name' => $session?->class?->class_name,
                    'section_id' => $session?->section_id,
                    'section_name' => $session?->section?->section_name,
                    'roll' => $session?->roll,
                ];

                return $this->responseSuccess($payload, 'Profile fetched successfully.');
            } else {
                return $this->responseError([], 'Profile not found.', 404);
            }
        } catch (Exception $e) {
            return $this->responseError([], 'An error occurred while fetching the profile.');
        }
    }

    public function profileUpdate(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,'.$request->user()->id,
            'phone' => 'sometimes|string|max:20',
        ]);

        $request->user()->fill($validated);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return $this->ResponseSuccess([], 'Profile Update Successfully Done.');
    }

    public function studentLogin(StudentLoginRequest $request)
    {
        // Get institute ID from header
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        // Validate input
        $validated = $request->validated();
        $phone = $validated['phone'];
        $password = $validated['password'];
        $deviceName = $validated['device_name'] ?? null;
        if (! $deviceName) {
            return $this->ResponseError([], 'Device Info is required.', 422);
        }

        // Get user
        $user = User::with('student.devices', 'student.deviceControl')
            ->where('phone', $phone)
            ->where('institute_id', $instituteId)
            ->where('user_type', 'Student')
            ->first();
        if (! $user || ! $user->student) {
            return $this->ResponseError([], 'Institute ID mismatch or student not found.', 403);
        }

        // Verify password
        if (! Hash::check($password, $user->password)) {
            return $this->ResponseError([], 'Invalid credentials.', 403);
        }

        // Blocked / deactivated students cannot sign in.
        if ((int) ($user->status ?? 0) !== 1) {
            return $this->ResponseError([], 'Your account is inactive. Please contact the institute administrator.', 403);
        }

        $student = $user->student;
        // Ensure device control exists or create it
        $deviceControl = $student->deviceControl;
        if (! $deviceControl) {
            $deviceControl = $student->deviceControl()->create([
                'device_access_type' => 'single',
                'device_limit' => 1,
                'is_active' => true,
            ]);
        }

        if (! $deviceControl->is_active) {
            return $this->ResponseError([], 'Device access for this student is currently disabled.', 403);
        }

        // Check if device is already registered
        $existingDevice = $student->devices->where('device_name', $deviceName)->first();
        if (! $existingDevice) {
            if ($student->devices->count() >= $deviceControl->device_limit) {
                return $this->ResponseError([], 'Device limit exceeded. Contact admin to register a new device.', 403);
            }

            // Register the new device
            $existingDevice = $student->devices()->create([
                'device_name' => $deviceName,
            ]);
        }

        // Create API token
        $tokenResult = $user->createToken('Student Personal Access Token');

        return $this->ResponseSuccess([
            'device_name' => $deviceName,
            'student_device_setting' => $deviceControl,
            'student_devices_uses' => $student->devices,
            'existing_device' => $existingDevice,
            'user' => $user,
            'expires_at' => $tokenResult->token->expires_at,
            'access_token' => $tokenResult->accessToken,
        ], 'Login successful.');
    }

    /**
     * Step 1: Send OTP to phone (Create user if not exists)
     */
    public function studentOtpLogin(StudentOtpLoginRequest $request)
    {
        $phone = $request->phone;
        // Get institute ID from header
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        // Check if user already exists
        $user = User::where('phone', $phone)
            ->where('institute_id', $instituteId)
            ->where('user_type', 'Student')
            ->first();
        // If user not exists, create one
        if (! $user) {
            return $this->ResponseError([], 'Student not found with this phone number.', 404);
        }

        // Generate OTP
        $otp = (string) random_int(100000, 999999);
        $expiryTime = now()->addMinutes(5);
        // Save or update OTP record
        OtpVerification::updateOrCreate(
            [
                'institute_id' => $instituteId,
                'phone' => $phone,
            ],
            [
                'otp' => $otp,
                'expires_at' => $expiryTime,
                'updated_at' => now(),
            ]
        );

        sent_sms($phone, "Your OTP is: {$otp}");

        return $this->ResponseSuccess([
            'phone' => $phone,
        ], 'OTP sent successfully.');
    }

    // Student
    public function studentOtpSent(Request $request)
    {
        $request->validate([
            'phone' => 'required|numeric|exists:users,phone',
        ]);

        $phone = $request->phone;
        $expiryTime = now()->addMinutes(2); // OTP expiry = 2 minutes
        $otp = random_int(100000, 999999); // Generate random 6-digit OTP

        // Check if an OTP record already exists for this phone
        $otpRecord = OtpVerification::where('phone', $phone)->first();

        if ($otpRecord) {
            // Check if existing OTP is still valid (not expired)
            if (now()->lessThan($otpRecord->expires_at)) {
                $remainingSeconds = now()->diffInSeconds($otpRecord->expires_at);

                return $this->ResponseError([
                    'remaining_seconds' => $remainingSeconds,
                ], "An OTP has already been sent. Please wait {$remainingSeconds} seconds before requesting a new one.", 429);
            }

            // OTP expired → generate and resend a new one
            $otpRecord->update([
                'otp' => $otp,
                'expires_at' => $expiryTime,
            ]);
        } else {
            // No record → create a new one
            OtpVerification::create([
                'phone' => $phone,
                'otp' => $otp,
                'expires_at' => $expiryTime,
            ]);
        }

        sent_sms($phone, "Your OTP is: {$otp}");

        return $this->ResponseSuccess([
            'phone' => $phone,
            'expires_at' => $expiryTime->toDateTimeString(),
        ], 'OTP sent successfully.');
    }

    public function studentOtpVerify(Request $request)
    {
        // ✅ Step 1: Get Institute ID from Header
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        // ✅ Step 2: Validate Request
        $validated = $request->validate([
            'phone' => 'required|numeric|exists:users,phone',
            'otp' => 'required',
            'device_name' => 'required|string|max:255',
        ]);

        $phone = $validated['phone'];
        $otp = $validated['otp'];
        $deviceName = $validated['device_name'];

        // ✅ Step 3: Check OTP Record
        $otpRecord = OtpVerification::where('phone', $phone)->first();

        if (! $otpRecord) {
            return $this->ResponseError([], 'Invalid phone number or OTP record not found.', 400);
        }

        // ✅ Step 4: Check Expiry
        if (now()->greaterThan($otpRecord->expires_at)) {
            $otpRecord->update(['otp' => null]);

            return $this->ResponseError([], 'OTP expired. Please request a new one.', 400);
        }

        // ✅ Step 5: Validate OTP
        if ($otpRecord->otp !== $otp) {
            return $this->ResponseError([], 'Invalid OTP.', 400);
        }

        // ✅ Step 6: Clear OTP after success
        $otpRecord->update(['otp' => null]);

        // ✅ Step 7: Find Student User
        $user = User::with(['student.devices', 'student.deviceControl'])
            ->where([
                'phone' => $phone,
                'institute_id' => $instituteId,
                'user_type' => 'Student',
            ])->first();

        if (! $user) {
            return $this->ResponseError([], 'Student user not found.', 404);
        }

        if ((int) ($user->status ?? 0) !== 1) {
            return $this->ResponseError([], 'Your account is inactive. Please contact the institute administrator.', 403);
        }

        $student = $user->student;

        // ✅ Step 8: Ensure Device Control Exists
        $deviceControl = $student->deviceControl ?? $student->deviceControl()->create([
            'device_access_type' => 'single',
            'device_limit' => 1,
            'is_active' => true,
        ]);

        if (! $deviceControl->is_active) {
            return $this->ResponseError([], 'Device access for this student is currently disabled.', 403);
        }

        // ✅ Step 9: Check Device Registration
        $existingDevice = $student->devices->where('device_name', $deviceName)->first();

        if (! $existingDevice) {
            if ($student->devices->count() >= $deviceControl->device_limit) {
                return $this->ResponseError([], 'Device limit exceeded. Contact admin to register a new device.', 403);
            }

            $existingDevice = $student->devices()->create([
                'device_name' => $deviceName,
            ]);
        }

        // ✅ Step 10: Generate Token
        $tokenResult = $user->createToken('Student Access Token');

        return $this->ResponseSuccess([
            'device_name' => $deviceName,
            'student_device_setting' => $deviceControl,
            'student_devices_uses' => $student->devices,
            'existing_device' => $existingDevice,
            'user' => $user,
            'expires_at' => $tokenResult->token->expires_at,
            'access_token' => $tokenResult->accessToken,
        ], 'Login successful.');
    }

    private function getInstituteIdFromHeader(Request $request)
    {
        $domain = $request->header('X-Domain'); // Custom header key (Change if needed)
        if (! $domain) {
            return $this->responseError([], 'Domain header is missing.', 400);
        }

        $instituteResponse = InstituteHelper::getInstituteIdByDomain($domain);
        if (! $instituteResponse['status']) {
            return $this->responseError([], $instituteResponse['error'], 404);
        }

        return $instituteResponse['institute_id'];
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return $this->ResponseSuccess([], 'Password Changes Successfully.');
    }

    public function accountDeleted(Request $request)
    {
        $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $user->tokens()->delete();
        $user->delete();

        return $this->ResponseSuccess([], 'Account Delete successfully.');
    }

    public function studentLogout(Request $request)
    {
        try {
            $result = $request->user()->token()->revoke();
            if ($result) {
                return $this->ResponseSuccess([], 'Logout Success');
            }
        } catch (Exception $e) {
            return $this->ResponseSuccess([], 'Logout Failed');
        }
    }
}
