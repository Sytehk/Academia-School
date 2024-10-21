<?php

use App\Http\Middleware\EnsureAttendanceDevice;
use Illuminate\Support\Facades\Route;
use Modules\SystemConfiguration\Http\Controllers\API\UserLogsController;
use Modules\SystemConfiguration\Http\Controllers\API\UtilityController;

Route::middleware('auth:api')->group(function () {
    // Utility Controller
    Route::post('/drop-database', [UtilityController::class, 'dropDatabase']);
    Route::match(['get', 'post'], 'administration/general_settings', [UtilityController::class, 'settings']);
    Route::post('administration-change-year', [UtilityController::class, 'academicYearChange']);
    Route::post('administration-change-branch', [UtilityController::class, 'changeBranch']);
    Route::post('administration-upload-logo', [UtilityController::class, 'uploadLogo']);
    Route::get('administration-backup-database', [UtilityController::class, 'backupDatabase']);
    Route::post('administration-backup-run', [UtilityController::class, 'runBackup']);
    Route::get('administration-backups', [UtilityController::class, 'listBackups']);
    Route::apiResource('user-logs', UserLogsController::class);
});

Route::get('get-image-folder-urls', [UtilityController::class, 'getAllImageFolderUrls']);

// Biometric / QR reader punch sync. Not an authenticated *user* endpoint — the
// device holds its institute's attendance_device_token instead, and the
// middleware pins the tenant from that token so the payload's QR codes can no
// longer reach across institutes. Throttled because it writes attendance rows
// and queues WhatsApp messages to parents.
Route::post('student-device-attendance', [UtilityController::class, 'studentDeviceAttendance'])
    ->middleware([
        EnsureAttendanceDevice::class,
        'throttle:'.config('attendance.device_rate_limit', '60,1'),
    ]);
