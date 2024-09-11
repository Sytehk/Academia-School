<?php

use Illuminate\Support\Facades\Route;
use Modules\Teacher\Http\Controllers\WEB\BehaviorController;
use Modules\Teacher\Http\Controllers\WEB\ClassLessonController;
use Modules\Teacher\Http\Controllers\WEB\GamificationController;
use Modules\Teacher\Http\Controllers\WEB\PrayerController;
use Modules\Teacher\Http\Controllers\WEB\QrAttendanceController;
use Modules\Teacher\Http\Controllers\WEB\ResourceController;
use Modules\Teacher\Http\Controllers\WEB\TeacherController;

/** Teacher Route Group **/
Route::group(['middleware' => ['auth', 'teacher']], function () {
    Route::get('teacher/qr-attendance', [QrAttendanceController::class, 'index']);
    Route::post('teacher/qr-attendance/scan', [QrAttendanceController::class, 'scan']);
    Route::get('teacher/my-profile', [TeacherController::class, 'my_profile']);
    Route::get('teacher/class-schedule', [TeacherController::class, 'class_schedule']);
    Route::get('teacher/assignments', [TeacherController::class, 'assignments']);
    Route::get('teacher/create-assignment', [TeacherController::class, 'create_assignment']);
    Route::post('teacher/store-assignment', [TeacherController::class, 'store_assignment']);
    Route::get('teacher/edit-assignment/{id}', [TeacherController::class, 'edit_assignment']);
    Route::get('teacher/assignment/{id}', [TeacherController::class, 'show_assignment']);
    Route::post('teacher/update-assignment/{id}', [TeacherController::class, 'update_assignment']);
    Route::get('teacher/destroy-assignment/{id}', [TeacherController::class, 'destroy_assignment']);
    Route::resource('resources', ResourceController::class);
    Route::resource('classlessons', ClassLessonController::class);
    Route::resource('prayers', PrayerController::class);
    Route::post('behaviors/students', [BehaviorController::class, 'getStudents'])->name('behaviors.students');
    Route::resource('behaviors', BehaviorController::class);
    Route::resource('gamifications', GamificationController::class);
});
