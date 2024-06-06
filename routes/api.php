<?php

use App\Http\Controllers\API\AdmissionNumberSettingsController;
use App\Http\Controllers\API\IdCardController;
use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::prefix('languages')->group(function () {
    Route::get('/', [LanguageController::class, 'index']);
    Route::post('/', [LanguageController::class, 'store']);
    Route::get('/export/{name}', [LanguageController::class, 'export']);
    Route::post('/update-key/{name}', [LanguageController::class, 'updateKey']);
    Route::post('/import/{name}', [LanguageController::class, 'import']);
    Route::post('/set-default/{name}', [LanguageController::class, 'setDefault']);
});

Route::get('/check-status', function () {
    try {
        DB::connection()->getPdo(); // test DB

        return response()->json([
            'status' => true,
            'message' => 'API and Database are working perfectly',
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
        ]);
    }
});

// Institute-wise admission number generation settings.
Route::middleware('auth:api')->group(function () {
    Route::get('admission-number-settings', [AdmissionNumberSettingsController::class, 'show']);
    Route::middleware('access:academic_admission_number_settings,academic_configuration_student_categories')->group(function () {
        Route::put('admission-number-settings', [AdmissionNumberSettingsController::class, 'update']);
    });
});

// Server-rendered ID card PDFs (dompdf).
Route::middleware('auth:api')->group(function () {
    Route::get('id-card-departments', [IdCardController::class, 'departments']);
    Route::middleware('access:id_card_student')->group(function () {
        Route::get('id-cards/students/pdf', [IdCardController::class, 'studentsPdf']);
    });
    Route::middleware('access:id_card_teacher')->group(function () {
        Route::get('id-cards/teachers/pdf', [IdCardController::class, 'teachersPdf']);
    });
    Route::middleware('access:id_card_staff')->group(function () {
        Route::get('id-cards/staff/pdf', [IdCardController::class, 'staffPdf']);
    });
});
