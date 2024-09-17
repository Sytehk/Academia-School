<?php

use Illuminate\Support\Facades\Route;
use Modules\Elearning\Http\Controllers\API\ChapterController;
use Modules\Elearning\Http\Controllers\API\ClassRoutineController;
use Modules\Elearning\Http\Controllers\API\ContentController;
use Modules\Elearning\Http\Controllers\API\ContentVisibilityController;
use Modules\Elearning\Http\Controllers\API\CourseCategoryController;
use Modules\Elearning\Http\Controllers\API\CourseController;
use Modules\Elearning\Http\Controllers\API\CourseFaqController;
use Modules\Elearning\Http\Controllers\API\CourseFeatureController;
use Modules\Elearning\Http\Controllers\API\DayController;
use Modules\Elearning\Http\Controllers\API\EnrollmentController;
use Modules\Elearning\Http\Controllers\API\LessonController;
use Modules\Elearning\Http\Controllers\API\RoomController;
use Modules\Elearning\Http\Controllers\API\ZoomController;
use Modules\Frontend\Http\Controllers\API\FrontendController;
use Modules\QuestionBank\Http\Controllers\API\QuestionCategoryController;
use Modules\QuestionBank\Http\Controllers\API\QuestionController;
use Modules\QuestionBank\Http\Controllers\API\QuizController;

Route::prefix('/frontend')->controller(FrontendController::class)->group(function () {
    Route::get('category-wise-courses', 'categoryWiseCourses');
    Route::get('courses', 'courses');
    Route::get('course-details/{slug}', 'courseDetails');
});

Route::middleware(['auth:api'])->group(function () {
    // Route::prefix('v1')->group(function () {
    // Course & Chapters & Lessons
    Route::get('course-category-list', [CourseCategoryController::class, 'courseCategoryList']);
    Route::get('course-subcategory-list/{categoryId}', [CourseCategoryController::class, 'courseSubCategoryList']);
    Route::apiResource('course-categories', CourseCategoryController::class);
    Route::get('course-list', [CourseController::class, 'courseList']);
    Route::post('course-status-update', [CourseController::class, 'courseStatusUpdate']);
    Route::get('course-details/{courseId}', [CourseController::class, 'courseDetails']);
    Route::apiResource('courses', CourseController::class);
    Route::apiResource('course-faqs', CourseFaqController::class);
    Route::apiResource('course-features', CourseFeatureController::class);

    Route::post('chapter-reorder', [ChapterController::class, 'chapterReorder']);
    Route::post('chapters-reorder-contents', [ChapterController::class, 'reorder']);
    Route::post('chapters-contents-delete', [ChapterController::class, 'contentDelete']);
    Route::apiResource('chapters', ChapterController::class);
    Route::apiResource('lessons', LessonController::class);

    // Content Visibility
    Route::post('content-visibility-bulk-toggle', [ContentVisibilityController::class, 'bulkToggleContentVisibility']);

    // Question - Quiz & Assignment & Quiz Attempt with Results
    Route::apiResource('question-categories', QuestionCategoryController::class);
    Route::apiResource('questions', QuestionController::class);

    Route::put('/quizzes/{id}/questions', [QuizController::class, 'updateQuestions']);
    Route::apiResource('quizzes', QuizController::class);

    // Bulk Student Enrollment
    Route::get('enrollments/list', [EnrollmentController::class, 'index']);
    Route::get('student-search', [EnrollmentController::class, 'studentSearch']);
    Route::post('/pay-due', [EnrollmentController::class, 'payDue']);
    Route::get('/payment-history/{studentId}', [EnrollmentController::class, 'getStudentPaymentHistory']);
    Route::get('/transactions/{studentId}', [EnrollmentController::class, 'getStudentTransactions']);

    // Device Control
    Route::post('device-control', [EnrollmentController::class, 'deviceControl']);
    Route::get('elearning-student-device-status/{studentId}', [EnrollmentController::class, 'studentDeviceStatus']);
    Route::get('elearning-student-device-delete/{deviceId}', [EnrollmentController::class, 'studentDeviceDelete']);

    // Zoom API
    Route::apiResource('course-zooms', ZoomController::class);

    // Teachers API
    Route::apiResource('course-rooms', RoomController::class);
    Route::apiResource('course-days', DayController::class);

    // routes/api.php
    Route::post('check-teacher-availability', [ClassRoutineController::class, 'checkTeacherAvailability']);
    Route::apiResource('course-class-routines', ClassRoutineController::class);
    Route::apiResource('contents', ContentController::class);
    // });
});
