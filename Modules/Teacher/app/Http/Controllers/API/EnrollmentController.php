<?php

namespace Modules\Teacher\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Elearning\Models\Enrollment;

/**
 * Teacher-wise enrollment list: enrollments for courses the authenticated
 * teacher created, mirroring the shape of the Admin enrollment list
 * (Modules\Elearning\Http\Controllers\API\EnrollmentController::index) but
 * additionally scoped to the teacher's own courses. Institute isolation is
 * inherited for free from Course's BelongsToTenant scope applied through the
 * whereHas('course', ...) below — a teacher can never see another
 * institute's enrollments even if a course id were guessed, because the
 * course query itself is tenant-scoped.
 */
class EnrollmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => 'nullable|integer',
            'student_id' => 'nullable|integer',
            'status' => 'nullable|in:active,inactive,blocked',
            'perPage' => 'nullable|integer|min:1|max:100',
        ]);

        $teacherId = Auth::id();

        $query = Enrollment::with(['user', 'course'])
            ->whereHas('course', fn ($q) => $q->where('created_by', $teacherId));

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('student_id')) {
            $query->where('user_id', $request->student_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $enrollments = $query->orderByDesc('enrollment_date')->paginate($request->input('perPage', 10));

        $data = $enrollments->map(function ($enrollment) {
            return [
                'enrollment_id' => $enrollment->id,
                'course_id' => $enrollment->course_id,
                'course' => $enrollment->course?->title ?? 'N/A',
                'student_id' => $enrollment->user_id,
                'student_name' => trim(($enrollment->user?->first_name ?? '').' '.($enrollment->user?->last_name ?? '')) ?: ($enrollment->user?->name ?? 'N/A'),
                'student_email' => $enrollment->user?->email,
                'student_phone' => $enrollment->user?->phone,
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
}
