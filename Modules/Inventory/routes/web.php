<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\WEB\CategoryController;
use Modules\Inventory\Http\Controllers\WEB\ItemController;
use Modules\Inventory\Http\Controllers\WEB\SaleController;

Route::middleware(['auth'])->group(function () {
    Route::resource('inventory-categories', CategoryController::class)->except('show');
    Route::resource('inventory-items', ItemController::class)->except('show');

    Route::get('inventory-sales', [SaleController::class, 'index'])->name('inventory-sales.index');
    Route::get('inventory-sales/create', [SaleController::class, 'create'])->name('inventory-sales.create');
    Route::post('inventory-sales', [SaleController::class, 'store'])->name('inventory-sales.store');
    Route::get('inventory-sales/{id}', [SaleController::class, 'show'])->name('inventory-sales.show');
});
