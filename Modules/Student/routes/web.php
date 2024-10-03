<?php

use Illuminate\Support\Facades\Route;
use Modules\Student\Http\Controllers\WEB\AssignmentController;
use Modules\Student\Http\Controllers\WEB\StudentDashboardController;

Route::group(['middleware' => ['auth', 'student']], function () {
    Route::get('student/qr-attendance', [StudentDashboardController::class, 'qr_attendance']);
    Route::get('student/my-profile', [StudentDashboardController::class, 'my_profile']);
    Route::get('student/my-subjects', [StudentDashboardController::class, 'my_subjects']);
    Route::get('student/class-routine', [StudentDashboardController::class, 'class_routine']);
    Route::match(['get', 'post'], 'student/exam_routine/{view?}', [StudentDashboardController::class, 'exam_routine']);
    Route::get('student/library-history', [StudentDashboardController::class, 'library_history']);
    Route::get('student/my-assignment', [StudentDashboardController::class, 'my_assignment']);
    Route::get('student/view-assignment/{id?}', [StudentDashboardController::class, 'view_assignment']);
    Route::resource('student/assignments-submit', AssignmentController::class);
    Route::delete('student/assignments-submit/{id}', [AssignmentController::class, 'destroy'])->name('student.assignments-submit.destroy');

    Route::get('student/my-syllabus', [StudentDashboardController::class, 'my_syllabus']);
    Route::get('student/view-syllabus/{id?}', [StudentDashboardController::class, 'view_syllabus']);
    Route::get('student-attendance-fine-report', [StudentDashboardController::class, 'studentAttendanceFineReport'])->name('student-attendance-fine-report');
    Route::get('student/quiz-fine-report', [StudentDashboardController::class, 'studentQuizFineReport'])->name('student-quiz-fine-report');
    Route::get('student/lab-fine-report', [StudentDashboardController::class, 'studentLabFineReport'])->name('student-lab-fine-report');
    Route::get('student/payment-fee-info', [StudentDashboardController::class, 'getPaymentInfoStudent'])->name('student-payment-fee-info');
    Route::get('student/payment-details-info/{id}', [StudentDashboardController::class, 'getPaymentInfoDetails'])->name('payment-details-info');
    Route::get('student/unpaid-info', [StudentDashboardController::class, 'getUnpaidFeeInfoStudent'])->name('student-unpaid-info');
    Route::get('student/feedback', [StudentDashboardController::class, 'getStudentNotification']);
    Route::get('student/zoom', [StudentDashboardController::class, 'getStudentZoom']);
    Route::get('student/google-meet', [StudentDashboardController::class, 'getStudentGoogleMeet'])->name('student.google-meet');
    Route::get('student/hostel', [StudentDashboardController::class, 'getStudentHostelProfile'])->name('student.hostel');
});
