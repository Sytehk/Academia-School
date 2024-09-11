<?php

use Illuminate\Support\Facades\Route;
use Modules\Teacher\Http\Controllers\API\BehaviorController;
use Modules\Teacher\Http\Controllers\API\ClassLessonController;
use Modules\Teacher\Http\Controllers\API\CourseController;
use Modules\Teacher\Http\Controllers\API\EnrollmentController;
use Modules\Teacher\Http\Controllers\API\GamificationController;
use Modules\Teacher\Http\Controllers\API\PortalLessonController;
use Modules\Teacher\Http\Controllers\API\PrayerController;
use Modules\Teacher\Http\Controllers\API\QrAttendanceController;
use Modules\Teacher\Http\Controllers\API\ResourceController;
use Modules\Teacher\Http\Controllers\API\TeacherController;

Route::middleware(['auth:api', 'teacher'])->group(function () {
    Route::get('teacher/qr-attendance/sections', [QrAttendanceController::class, 'sections']);
    Route::post('teacher/qr-attendance/scan', [QrAttendanceController::class, 'scan']);

    Route::get('teacher/courses', [CourseController::class, 'index']);
    Route::post('teacher/courses', [CourseController::class, 'store']);
    Route::get('teacher/courses/{id}', [CourseController::class, 'show'])->whereNumber('id');
    Route::put('teacher/courses/{id}', [CourseController::class, 'update'])->whereNumber('id');
    Route::delete('teacher/courses/{id}', [CourseController::class, 'destroy'])->whereNumber('id');
    Route::get('teacher/enrollments', [EnrollmentController::class, 'index']);
    Route::get('teacher/my-profile', [TeacherController::class, 'my_profile']);
    Route::get('teacher/class-schedule', [TeacherController::class, 'class_schedule']);
    Route::get('teacher/assignments', [TeacherController::class, 'assignments']);
    Route::get('teacher/create-assignment', [TeacherController::class, 'create_assignment']);
    Route::post('teacher/store-assignment', [TeacherController::class, 'store_assignment']);
    Route::get('teacher/edit-assignment/{id}', [TeacherController::class, 'edit_assignment']);
    Route::get('teacher/assignment/{id}', [TeacherController::class, 'show_assignment']);
    Route::post('teacher/update-assignment/{id}', [TeacherController::class, 'update_assignment']);
    Route::get('teacher/destroy-assignment/{id}', [TeacherController::class, 'destroy_assignment']);

    // Core Setting
    Route::get('teacher/notices', [TeacherController::class, 'teacherNoticeGet']);
    Route::get('teacher/events', [TeacherController::class, 'teacherEventGet']);

    Route::apiResource('classlessons', ClassLessonController::class);
    Route::post('classlessons/{id}/publish', [ClassLessonController::class, 'publish'])->whereNumber('id');
    Route::post('classlessons/{id}/unpublish', [ClassLessonController::class, 'unpublish'])->whereNumber('id');
    Route::post('classlessons/{id}/attachments', [ClassLessonController::class, 'addAttachments'])->whereNumber('id');
    Route::delete('classlesson-attachments/{attachmentId}', [ClassLessonController::class, 'destroyAttachment'])->whereNumber('attachmentId');
    Route::apiResource('prayers', PrayerController::class);
    Route::apiResource('behaviors', BehaviorController::class);
    Route::apiResource('gamifications', GamificationController::class);
    Route::apiResource('resources', ResourceController::class);
});

// Back-office lesson plans (all lessons of the branch).
Route::middleware('auth:api')->group(function () {
    Route::middleware('access:lesson_plan_view,lesson_plan_manage')->group(function () {
        Route::get('lesson-plans', [ClassLessonController::class, 'index']);
        Route::get('lesson-plans/{id}', [ClassLessonController::class, 'show'])->whereNumber('id');
    });
    Route::middleware('access:lesson_plan_manage')->group(function () {
        Route::post('lesson-plans', [ClassLessonController::class, 'store']);
        Route::put('lesson-plans/{id}', [ClassLessonController::class, 'update'])->whereNumber('id');
        Route::delete('lesson-plans/{id}', [ClassLessonController::class, 'destroy'])->whereNumber('id');
        Route::post('lesson-plans/{id}/publish', [ClassLessonController::class, 'publish'])->whereNumber('id');
        Route::post('lesson-plans/{id}/unpublish', [ClassLessonController::class, 'unpublish'])->whereNumber('id');
        Route::post('lesson-plans/{id}/attachments', [ClassLessonController::class, 'addAttachments'])->whereNumber('id');
        Route::delete('lesson-plan-attachments/{attachmentId}', [ClassLessonController::class, 'destroyAttachment'])->whereNumber('attachmentId');
    });
});

// Student / parent portals: published lessons of the student's own class and section.
Route::middleware(['auth:api', 'student'])->group(function () {
    Route::get('student/lessons', [PortalLessonController::class, 'index']);
    Route::get('student/lessons/{id}', [PortalLessonController::class, 'show'])->whereNumber('id');
});
Route::prefix('parent/my-children')->middleware(['auth:api', 'parent'])->group(function () {
    Route::get('lessons', [PortalLessonController::class, 'index']);
    Route::get('lessons/{id}', [PortalLessonController::class, 'show'])->whereNumber('id');
});
