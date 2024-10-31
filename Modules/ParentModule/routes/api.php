<?php

use Illuminate\Support\Facades\Route;
use Modules\ParentModule\Http\Controllers\API\APIParentController;
use Modules\ParentModule\Http\Controllers\API\ParentModuleController;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('parents', APIParentController::class);
});

/** Parent Route Group **/
Route::prefix('parent')->middleware(['auth:api', 'parent'])->controller(ParentModuleController::class)->group(function () {
    // Parent Profile (self-service — always the authenticated parent, no id)
    Route::get('my-profile', 'myProfile');
    Route::post('profile-update', 'updateProfile');
    Route::post('change-password', 'changePassword');

    // My Children (Detailed)
    Route::prefix('my-children')->controller(ParentModuleController::class)->group(function () {
        // My Children (Basic)
        Route::get('list', 'myChildrenList');
        Route::get('default-child-get', 'defaultChildGet');
        Route::post('default-child-assign', 'defaultChildAssign');
        Route::get('dashboard-data', 'dashboardData');
        Route::get('profile', 'myChildrenProfile');
        Route::post('attendance', 'childrenAttendance');
        Route::get('subjects', 'mySubjects');
        Route::get('class-routine', 'classRoutine');
        Route::get('exam-routine', 'examRoutine');
        Route::get('assignment', 'myAssignment');
        Route::get('assignment-submit', 'assignmentSubmit');
        Route::get('attendance-fine-report', 'studentAttendanceFineReport');
        Route::get('payment-fee-info', 'getPaymentInfoStudent');
        Route::get('unpaid-info', 'getUnpaidFeeInfoStudent');
        Route::get('notices', 'studentNoticeGet');
        Route::get('events', 'studentEventGet');
        Route::get('behaviors', 'studentBehaviorGet');
        Route::get('gamifications', 'studentGamificationGet');
        Route::get('prayers', 'studentPrayerGet');
        Route::get('library-history', 'libraryHistory');
        Route::get('subject-list', 'subjectList');
        Route::get('exam-list', 'examList');
        Route::post('exam-results', 'examResult');
    });
});
