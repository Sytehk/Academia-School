<?php

use Illuminate\Support\Facades\Route;
use Modules\LayoutCert\Http\Controllers\WEB\CertificateTemplateController;
use Modules\LayoutCert\Http\Controllers\WEB\LayoutCertController;

Route::group(['middleware' => ['auth']], function () {
    Route::get('layout-cert', [LayoutCertController::class, 'index'])->name('layout-cert.index');
    Route::post('layout-cert', [LayoutCertController::class, 'store'])->name('layout-cert.store');
    Route::post('layout-cert/students', [LayoutCertController::class, 'students'])->name('layout-cert.students');

    Route::get('certificate-templates', [CertificateTemplateController::class, 'index'])->name('certificate-templates.index');
    Route::get('certificate-templates/create', [CertificateTemplateController::class, 'create'])->name('certificate-templates.create');
    Route::post('certificate-templates', [CertificateTemplateController::class, 'store'])->name('certificate-templates.store');
    Route::get('certificate-templates/{id}/edit', [CertificateTemplateController::class, 'edit'])->name('certificate-templates.edit');
    Route::put('certificate-templates/{id}', [CertificateTemplateController::class, 'update'])->name('certificate-templates.update');
    Route::post('certificate-templates/{id}/duplicate', [CertificateTemplateController::class, 'duplicate'])->name('certificate-templates.duplicate');
    Route::post('certificate-templates/{id}/activate', [CertificateTemplateController::class, 'activate'])->name('certificate-templates.activate');
    Route::delete('certificate-templates/{id}', [CertificateTemplateController::class, 'destroy'])->name('certificate-templates.destroy');
});

// Public certificate verification — no auth, reached by scanning the QR code printed on a
// certificate. Deliberately outside the auth group above.
Route::get('certificate-verify/{code}', [LayoutCertController::class, 'verify'])->name('certificate.verify');
