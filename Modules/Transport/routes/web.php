<?php

use Illuminate\Support\Facades\Route;
use Modules\Transport\Http\Controllers\WEB\BusController;
use Modules\Transport\Http\Controllers\WEB\BusRouteController;
use Modules\Transport\Http\Controllers\WEB\BusStopController;
use Modules\Transport\Http\Controllers\WEB\DriverController;
use Modules\Transport\Http\Controllers\WEB\HelperController;
use Modules\Transport\Http\Controllers\WEB\TransportCollectionController;
use Modules\Transport\Http\Controllers\WEB\TransportDashboardController;
use Modules\Transport\Http\Controllers\WEB\TransportFeeReportController;
use Modules\Transport\Http\Controllers\WEB\TransportMemberController;
use Modules\Transport\Http\Controllers\WEB\VehicleCategoryController;
use Modules\Transport\Http\Controllers\WEB\VehicleTypeController;

/** Transport Route Group **/
Route::group(['middleware' => ['auth']], function () {
    Route::resource('buses', BusController::class);
    Route::resource('drivers', DriverController::class);
    Route::resource('bus-routes', BusRouteController::class);
    Route::resource('bus-stops', BusStopController::class);
    Route::resource('transport-members', TransportMemberController::class);
    Route::get('transport-members/{id}/card', [TransportMemberController::class, 'card'])->name('transport-members.card');

    Route::resource('vehicle-types', VehicleTypeController::class)->except(['show']);
    Route::resource('vehicle-categories', VehicleCategoryController::class)->except(['show']);
    Route::resource('helpers', HelperController::class)->except(['show']);

    Route::get('transport-dashboard', [TransportDashboardController::class, 'index'])->name('transport-dashboard.index');

    Route::get('transport-collections', [TransportCollectionController::class, 'index'])->name('transport-collections.index');
    Route::get('transport-collections/create/{transportMemberId}', [TransportCollectionController::class, 'create'])->name('transport-collections.create');
    Route::post('transport-collections', [TransportCollectionController::class, 'store'])->name('transport-collections.store');
    Route::get('transport-collections/{id}/invoice', [TransportCollectionController::class, 'invoice'])->name('transport-collections.invoice');

    Route::prefix('transport-reports')->name('transport-reports.')->group(function () {
        Route::get('collection', [TransportFeeReportController::class, 'collectionReport'])->name('collection');
        Route::get('due', [TransportFeeReportController::class, 'dueReport'])->name('due');
        Route::get('route-wise', [TransportFeeReportController::class, 'routeWiseReport'])->name('route-wise');
        Route::get('vehicle-wise', [TransportFeeReportController::class, 'vehicleWiseReport'])->name('vehicle-wise');
        Route::get('export/pdf', [TransportFeeReportController::class, 'exportPdf'])->name('export-pdf');
        Route::get('export/excel', [TransportFeeReportController::class, 'exportExcel'])->name('export-excel');
    });
});
