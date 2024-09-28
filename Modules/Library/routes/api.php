<?php

use Illuminate\Support\Facades\Route;
use Modules\Library\Http\Controllers\API\APILibraryController;
use Modules\Library\Http\Controllers\API\BookCategoriesController;
use Modules\Library\Http\Controllers\API\BookController;

Route::middleware('auth:api')->group(function () {
    // BookCategory - Book  Controller
    Route::apiResource('book-categories', BookCategoriesController::class);
    Route::apiResource('books', BookController::class);

    Route::get('books-issue', [APILibraryController::class, 'index']);
    Route::post('books-issue', [APILibraryController::class, 'store']);
    Route::post('books-return', [APILibraryController::class, 'bookReturn']);
    Route::get('books-issue-reports', [APILibraryController::class, 'filterAllIssues']);
});
