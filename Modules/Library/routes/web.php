<?php

use Illuminate\Support\Facades\Route;
use Modules\Library\Http\Controllers\WEB\BookBerCodeController;
use Modules\Library\Http\Controllers\WEB\BookCategoriesController;
use Modules\Library\Http\Controllers\WEB\BookController;
use Modules\Library\Http\Controllers\WEB\BookIssueController;
use Modules\Library\Http\Controllers\WEB\LibraryMemberController;

Route::group(['middleware' => ['auth']], function () {
    // Library Controller
    Route::get('librarymembers-librarycard/{id}', [LibraryMemberController::class, 'library_card'])->name('librarymembers.view_library_card');
    Route::post('librarymembers-section', [LibraryMemberController::class, 'get_section']);
    Route::post('librarymembers-student', [LibraryMemberController::class, 'get_student']);
    Route::resource('librarymembers', LibraryMemberController::class);

    // BookCategory - Book  Controller
    Route::resource('book-categories', BookCategoriesController::class);
    Route::resource('books', BookController::class);
    Route::get('books-bulk-upload', [BookController::class, 'bulkUpload'])->name('books.bulk-upload');
    Route::post('books-bulk-upload/store', [BookController::class, 'bulkStore'])->name('books.bulk-upload.store');
    Route::get('books-bulk-demo-file-download', [BookController::class, 'downloadDemoFile'])->name('books.bulk-upload-demo.download');
    Route::get('books-ber-code-page', [BookBerCodeController::class, 'index'])->name('books.ber-code');
    Route::get('books-ber-code-print', [BookBerCodeController::class, 'printPage'])->name('books.ber-code-print');
    Route::post('books-ber-code-print', [BookBerCodeController::class, 'printBooks'])->name('books.ber-code-print.store');
    Route::post('book-ber-code/store', [BookBerCodeController::class, 'store'])->name('book.search');
    Route::post('member-search/store', [BookBerCodeController::class, 'memberSearch'])->name('member.search');

    // Book Issue  Controller
    Route::match(['get', 'post'], 'bookissues-list/{library_id?}', [BookIssueController::class, 'index'])->name('bookissues.list');
    Route::get('bookissues/return/{id}', [BookIssueController::class, 'book_return'])->name('bookissues.return');
    Route::post('books-issue-multiple-return', [BookIssueController::class, 'bookIssueMultipleReturn'])->name('books-issue-multiple.return');
    Route::resource('bookissues', BookIssueController::class);
    Route::get('bookissues-filter-all', [BookIssueController::class, 'allIssues'])->name('bookissues.filter');
    Route::post('bookissues-filter-all', [BookIssueController::class, 'filterIssues'])->name('bookissues.filter_by_option');
    Route::post('multiple-book-issue-store', [BookIssueController::class, 'multipleBookIssue'])->name('multiple-book-issue.store');
    Route::post('books-issue-report-pdf-download', [BookIssueController::class, 'issueReport'])->name('books-issue-report-pdf-download');
});
