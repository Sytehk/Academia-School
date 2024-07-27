<?php

use Illuminate\Support\Facades\Route;
use Modules\Hostel\Http\Controllers\API\HostelBillController;
use Modules\Hostel\Http\Controllers\API\HostelCategoryController;
use Modules\Hostel\Http\Controllers\API\HostelController;
use Modules\Hostel\Http\Controllers\API\HostelMemberController;
use Modules\Hostel\Http\Controllers\API\MealController;
use Modules\Hostel\Http\Controllers\API\MealEntryController;
use Modules\Hostel\Http\Controllers\API\MealPlanController;
use Modules\Hostel\Http\Controllers\API\RoomController;
use Modules\Hostel\Http\Controllers\API\RoomMemberController;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('hostels', HostelController::class);
    Route::apiResource('hostel-categories', HostelCategoryController::class);
    Route::apiResource('hostel-members', HostelMemberController::class);
    Route::apiResource('rooms', RoomController::class);
    Route::apiResource('room-members', RoomMemberController::class);
    Route::apiResource('meals', MealController::class);
    Route::apiResource('meal-plans', MealPlanController::class);
    Route::apiResource('meal-entries', MealEntryController::class);
    Route::apiResource('hostel-bills', HostelBillController::class);
});
