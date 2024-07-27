<?php

use Illuminate\Support\Facades\Route;
use Modules\Hostel\Http\Controllers\WEB\BedController;
use Modules\Hostel\Http\Controllers\WEB\HostelBillController;
use Modules\Hostel\Http\Controllers\WEB\HostelBuildingController;
use Modules\Hostel\Http\Controllers\WEB\HostelCategoryController;
use Modules\Hostel\Http\Controllers\WEB\HostelCollectionController;
use Modules\Hostel\Http\Controllers\WEB\HostelController;
use Modules\Hostel\Http\Controllers\WEB\HostelDashboardController;
use Modules\Hostel\Http\Controllers\WEB\HostelFeeReportController;
use Modules\Hostel\Http\Controllers\WEB\HostelFloorController;
use Modules\Hostel\Http\Controllers\WEB\HostelLeaveController;
use Modules\Hostel\Http\Controllers\WEB\HostelMemberController;
use Modules\Hostel\Http\Controllers\WEB\MealController;
use Modules\Hostel\Http\Controllers\WEB\MealEntryController;
use Modules\Hostel\Http\Controllers\WEB\MealPlanController;
use Modules\Hostel\Http\Controllers\WEB\RoomController;
use Modules\Hostel\Http\Controllers\WEB\RoomMemberController;
use Modules\Hostel\Http\Controllers\WEB\SeatMapController;

/** Hostel Route Group **/
Route::group(['middleware' => ['auth']], function () {
    Route::resource('hostels', HostelController::class);
    Route::resource('hostel-categories', HostelCategoryController::class);
    Route::resource('hostel-members', HostelMemberController::class);
    Route::resource('rooms', RoomController::class);
    Route::resource('room-members', RoomMemberController::class);
    Route::get('room-members/{id}/profile', [RoomMemberController::class, 'profile'])->name('room-members.profile');
    Route::get('room-members/{id}/card', [RoomMemberController::class, 'card'])->name('room-members.card');
    Route::resource('meals', MealController::class);
    Route::resource('meal-plans', MealPlanController::class);
    Route::resource('meal-entries', MealEntryController::class);
    Route::resource('hostel-bills', HostelBillController::class);

    Route::resource('hostel-buildings', HostelBuildingController::class)->except(['show']);
    Route::resource('hostel-floors', HostelFloorController::class)->except(['show']);
    Route::resource('beds', BedController::class)->except(['show']);

    Route::resource('hostel-leaves', HostelLeaveController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::put('hostel-leaves/{id}/status', [HostelLeaveController::class, 'updateStatus'])->name('hostel-leaves.status');

    Route::get('hostel-dashboard', [HostelDashboardController::class, 'index'])->name('hostel-dashboard.index');
    Route::get('hostel-seat-map', [SeatMapController::class, 'index'])->name('hostel-seat-map.index');

    Route::get('hostel-collections', [HostelCollectionController::class, 'index'])->name('hostel-collections.index');
    Route::get('hostel-collections/create/{roomMemberId}', [HostelCollectionController::class, 'create'])->name('hostel-collections.create');
    Route::post('hostel-collections', [HostelCollectionController::class, 'store'])->name('hostel-collections.store');
    Route::get('hostel-collections/{id}/invoice', [HostelCollectionController::class, 'invoice'])->name('hostel-collections.invoice');

    Route::prefix('hostel-reports')->name('hostel-reports.')->group(function () {
        Route::get('collection', [HostelFeeReportController::class, 'collectionReport'])->name('collection');
        Route::get('due', [HostelFeeReportController::class, 'dueReport'])->name('due');
        Route::get('hostel-wise', [HostelFeeReportController::class, 'hostelWiseReport'])->name('hostel-wise');
        Route::get('building-wise', [HostelFeeReportController::class, 'buildingWiseReport'])->name('building-wise');
        Route::get('room-wise', [HostelFeeReportController::class, 'roomWiseReport'])->name('room-wise');
        Route::get('export/pdf', [HostelFeeReportController::class, 'exportPdf'])->name('export-pdf');
        Route::get('export/excel', [HostelFeeReportController::class, 'exportExcel'])->name('export-excel');
    });
});
