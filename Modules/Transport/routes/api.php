<?php

use Illuminate\Support\Facades\Route;
use Modules\Transport\Http\Controllers\API\BusController;
use Modules\Transport\Http\Controllers\API\BusRouteController;
use Modules\Transport\Http\Controllers\API\BusStopController;
use Modules\Transport\Http\Controllers\API\DriverController;
use Modules\Transport\Http\Controllers\API\TransportMemberController;

Route::middleware('auth:api')->group(function () {
    Route::apiResource('buses', BusController::class);
    Route::apiResource('drivers', DriverController::class);
    Route::apiResource('bus-routes', BusRouteController::class);
    Route::apiResource('bus-stops', BusStopController::class);
    Route::apiResource('transport-members', TransportMemberController::class);
});
