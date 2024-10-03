<?php

use Illuminate\Support\Facades\Route;
use Modules\QuestionBank\Http\Controllers\API\QuizAttemptController;
use Modules\Student\Http\Controllers\API\AssignmentController;
use Modules\Student\Http\Controllers\API\StudentAuthController;
use Modules\Student\Http\Controllers\API\StudentController;
use Modules\Student\Http\Controllers\API\StudentDashboardController;
use Modules\Student\Http\Controllers\API\StudentQuizController;
use Modules\Teacher\Http\Controllers\API\PortalLessonController;

Route::middleware(['auth:api', 'student'])->group(function () {
    Route::get('student/qr-attendance', [StudentDashboardController::class, 'qrAttendance']);
    Route::get('student/my-profile', [StudentDashboardController::class, 'my_profile']);
    Route::post('student/profile-update', [StudentDashboardController::class, 'profileUpdate']);
    Route::get('student/my-subjects', [StudentDashboardController::class, 'my_subjects']);
    Route::get('student/class-routine', [StudentDashboardController::class, 'class_routine']);
    Route::match(['get', 'post'], 'student/exam_routine/{view?}', [StudentDashboardController::class, 'exam_routine']);
    Route::get('student/library-history', [StudentDashboardController::class, 'library_history']);
    Route::get('student/my-assignment', [StudentDashboardController::class, 'my_assignment']);
    Route::get('student/view-assignment/{id?}', [StudentDashboardController::class, 'view_assignment']);

    Route::apiResource('student/assignments-submit', AssignmentController::class);
    Route::delete('student/assignments-submit/{id}', [AssignmentController::class, 'destroy']);

    Route::get('student/my-syllabus', [StudentDashboardController::class, 'my_syllabus']);
    Route::get('student/view-syllabus/{id?}', [StudentDashboardController::class, 'view_syllabus']);
    Route::get('student/attendance-fine-report', [StudentDashboardController::class, 'studentAttendanceFineReport']);
    Route::get('student/quiz-fine-report', [StudentDashboardController::class, 'studentQuizFineReport']);
    Route::get('student/lab-fine-report', [StudentDashboardController::class, 'studentLabFineReport']);
    Route::get('student/payment-fee-info', [StudentDashboardController::class, 'getPaymentInfoStudent']);
    Route::get('student/unpaid-info', [StudentDashboardController::class, 'getUnpaidFeeInfoStudent']);

    // Quiz Functionality
    Route::get('student/quiz', [StudentQuizController::class, 'quizPage']);
    Route::get('student/start_quiz/{id}', [StudentQuizController::class, 'quizStart']);
    Route::get('student/start_quiz/{id}/finish', [StudentQuizController::class, 'quizFinish']);

    Route::post('quiz-start', [QuizAttemptController::class, 'startQuiz']);
    Route::get('quiz-details/{quizId}', [QuizAttemptController::class, 'quizDetails']);
    Route::post('quiz-submit', [QuizAttemptController::class, 'quizSubmit']);
    Route::post('quiz-results', [QuizAttemptController::class, 'quizResults']);

    // Core Setting
    Route::get('student/notices', [StudentDashboardController::class, 'studentNoticeGet']);
    Route::get('student/events', [StudentDashboardController::class, 'studentEventGet']);

    Route::get('student/behaviors', [StudentDashboardController::class, 'studentBehaviorGet']);
    Route::get('student/classlessons', [PortalLessonController::class, 'index']);
    Route::get('student/prayers', [StudentDashboardController::class, 'studentPrayerGet']);
    Route::get('student/gamifications', [StudentDashboardController::class, 'studentGamificationGet']);
    Route::get('student/resources', [StudentDashboardController::class, 'studentResourcesGet']);

    // Student Profile
    Route::get('student', [StudentAuthController::class, 'studentProfile']);
    Route::get('student-profile', [StudentAuthController::class, 'studentProfile']);
    Route::post('student-update', [StudentAuthController::class, 'profileUpdate']);
    Route::post('student-change-password', [StudentAuthController::class, 'changePassword']);
    Route::post('student-account-delete', [StudentAuthController::class, 'accountDeleted']);
    Route::post('student-logout', [StudentAuthController::class, 'studentLogout']);

    Route::get('student/courses', [StudentController::class, 'availableCourses']);
    Route::get('student/courses/{id}', [StudentController::class, 'courseDetails'])->whereNumber('id');
    Route::post('enrollment-course', [StudentController::class, 'enrollmentCourse']);
    Route::get('student/my-course', [StudentController::class, 'myCourse']);
    Route::get('student/my-course-details/{slug}', [StudentController::class, 'myCourseDetails']);
    Route::post('course-content-details', [StudentController::class, 'courseContentDetails']);
    Route::get('student/my-transactions', [StudentController::class, 'myTransactions']);
    Route::get('student/my-attendance', [StudentController::class, 'myAttendance']);
});

// Student Auth — rate-limited to slow down credential/OTP brute-forcing
Route::middleware('throttle:6,1')->group(function () {
    Route::post('v1/student-login', [StudentAuthController::class, 'studentLogin']);
    Route::post('v1/student-otp-login', [StudentAuthController::class, 'studentOtpLogin']);
    Route::post('v1/student-otp-verify', [StudentAuthController::class, 'studentOtpVerify']);
    Route::post('v1/student-otp-sent', [StudentAuthController::class, 'studentOtpSent']);
});
