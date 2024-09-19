<?php

namespace Modules\Elearning\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\Authenticatable;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Academic\Models\DeviceControl;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\UserDevice;
use Modules\Authentication\Models\User;
use Modules\Elearning\Models\Enrollment;

class EnrollmentController extends Controller
{
    use Authenticatable, RequestSanitizerTrait;

    public function index(Request $request): JsonResponse
    {
        $query = Enrollment::with(['user', 'course']);

        // Apply Student filter if provided
        if ($request->has('student_id')) {
            $query->where('user_id', $request->student_id);
        }

        // Apply Course filter if provided
        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Fetch paginated results (10 per page)
        $enrollments = $query->paginate($request->perPage ?? 10);

        // Format response data
        $data = $enrollments->map(function ($enrollment) {
            return [
                'enrollment_id' => $enrollment->id,
                'course_id' => $enrollment->course_id,
                'student_id' => $enrollment->user_id,
                'student_name' => $enrollment->user?->first_name.' '.$enrollment->user?->last_name ?? 'N/A',
                'student_image' => $enrollment->user?->avatar ? url(Storage::url($enrollment->user->avatar)) : null,
                'student_phone' => $enrollment->user?->phone,
                'student_email' => $enrollment->user?->email,
                'course' => $enrollment->course?->title ?? 'N/A',
                'enrollment_type' => $enrollment->enrollment_type,
                'enrollment_date' => $enrollment->enrollment_date,
                'subscription_start' => $enrollment->subscription_start,
                'subscription_end' => $enrollment->subscription_end,
                'amount_paid' => $enrollment->amount_paid,
                'status' => $enrollment->status,
            ];
        });

        return $this->responseSuccess([
            'enrollments' => $data,
            'pagination' => [
                'total' => $enrollments->total(),
                'per_page' => $enrollments->perPage(),
                'current_page' => $enrollments->currentPage(),
                'last_page' => $enrollments->lastPage(),
            ],
        ], 'Enrollment list fetched successfully.');
    }

    public function studentSearch(Request $request): JsonResponse
    {
        $students = User::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('user_type', 'Student')
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = $request->string('search');
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->get();

        return $students->isNotEmpty()
            ? $this->responseSuccess($students, 'Students retrieved successfully.')
            : $this->responseSuccess([], 'No students found.');
    }

    public function deviceControl(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'device_access_type' => 'required|string|in:single,multiple,unlimited',
            'device_limit' => 'nullable|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $studentId = (int) $request->student_id;
            $deviceAccessType = $request->device_access_type;
            $deviceLimit = $request->device_limit;

            // Check if the user exists
            $user = Student::where('institute_id', $this->getCurrentInstituteId())->where('id', $studentId)->first();
            if (! $user) {
                return $this->responseError([], 'Invalid user.', 400);
            }

            // Update or create device control settings
            $deviceControl = DeviceControl::updateOrCreate(
                ['student_id' => $studentId],
                [
                    'device_access_type' => $deviceAccessType,
                    'device_limit' => $deviceAccessType === 'multiple' ? $deviceLimit : 1,
                    'is_active' => true,
                ]
            );

            DB::commit();

            return $this->responseSuccess($deviceControl, 'Device control settings updated successfully.');
        } catch (Exception $e) {
            DB::rollback();

            return $this->responseError([], 'Device control update failed: '.$e->getMessage(), 500);
        }
    }

    public function studentDeviceStatus($studentId)
    {
        $user = Student::where('institute_id', $this->getCurrentInstituteId())->with('user')->where('id', $studentId)->first();
        if (! $user) {
            return $this->responseError([], 'Invalid user.', 400);
        }

        $deviceControl = DeviceControl::where('student_id', $studentId)->first();
        if (! $deviceControl) {
            return $this->responseError([], 'No device control settings found for this user.', 404);
        }

        // Toggle status
        $deviceControl->is_active = ! $deviceControl->is_active;
        $deviceControl->save();

        // 🔴 Revoke all tokens only if disabling
        if (! $deviceControl->is_active) {
            $studentUser = $user->user; // assuming user is related to User model
            if ($studentUser) {
                // 1. Delete from oauth_access_tokens
                DB::table('oauth_access_tokens')
                    ->where('user_id', $studentUser->id)
                    ->delete();

                // 2. (Optional) Also delete associated refresh tokens
                DB::table('oauth_refresh_tokens')
                    ->whereIn('access_token_id', function ($query) use ($studentUser) {
                        $query->select('id')
                            ->from('oauth_access_tokens')
                            ->where('user_id', $studentUser->id);
                    })
                    ->delete();
            }
        }

        return $this->responseSuccess([
            'new_status' => $deviceControl->is_active,
            'message' => $deviceControl->is_active ? 'Device access enabled.' : 'Device access disabled.',
        ], 'Device status updated successfully.');
    }

    public function studentDeviceDelete($deviceId): JsonResponse
    {
        $studentDevice = UserDevice::where('id', $deviceId)->first();
        if (! $studentDevice) {
            return $this->responseError([], 'Student Device not found.', 404);
        }

        $studentUser = $studentDevice->user?->user ?? null;
        if (! $studentUser) {
            return $this->responseError([], 'Student User not found.', 404);
        }
        if ($studentUser) {
            // 1. Delete from oauth_access_tokens
            DB::table('oauth_access_tokens')
                ->where('user_id', $studentUser->id)
                ->delete();

            // 2. (Optional) Also delete associated refresh tokens
            DB::table('oauth_refresh_tokens')
                ->whereIn('access_token_id', function ($query) use ($studentUser) {
                    $query->select('id')
                        ->from('oauth_access_tokens')
                        ->where('user_id', $studentUser->id);
                })
                ->delete();
        }

        // Delete the device control record
        $studentDevice->delete();

        return $this->responseSuccess([], 'Student Device deleted successfully.');
    }
}
