<?php

use Illuminate\Support\Facades\Route;
use Modules\LayoutCert\Http\Controllers\API\CertificateController;
use Modules\LayoutCert\Http\Controllers\API\LayoutCertController;

Route::middleware('auth:api')->group(function () {
    Route::get('layout-certificates', [LayoutCertController::class, 'index']);

    Route::get('certificate-types', [CertificateController::class, 'types']);
    Route::get('certificate-templates-list', [CertificateController::class, 'templates']);

    Route::middleware('access:layouts_certificates_general,layouts_certificates_testimonial,layouts_certificates_attendance_certificate,layouts_certificates_hsc_recommendation_letter,layouts_certificates_transfer,layouts_certificates_abroad_letter,layouts_certificates_character,layouts_certificates_study,layouts_certificates_bonafide,layouts_certificates_migration')->group(function () {
        Route::post('certificates/issue', [CertificateController::class, 'issue']);
        Route::get('certificates/{issueId}/pdf', [CertificateController::class, 'pdf'])->whereNumber('issueId')->name('certificates.pdf');
    });

    Route::middleware('access:layouts_certificates_bulk_issue')->group(function () {
        Route::post('certificates/bulk-issue', [CertificateController::class, 'bulkIssue']);
    });
});

// Public certificate verification — no auth, reached by scanning the QR code.
Route::get('certificate-verify/{code}', [CertificateController::class, 'verify']);
