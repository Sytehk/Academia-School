<?php

use Illuminate\Support\Facades\Route;
use Modules\Frontend\Http\Controllers\WEB\AboutUsController;
use Modules\Frontend\Http\Controllers\WEB\AcademicImageController;
use Modules\Frontend\Http\Controllers\WEB\AdmissionFormController;
use Modules\Frontend\Http\Controllers\WEB\AdmissionFormFrontendController;
use Modules\Frontend\Http\Controllers\WEB\BannerController;
use Modules\Frontend\Http\Controllers\WEB\FaqQuestionController;
use Modules\Frontend\Http\Controllers\WEB\FrontendController;
use Modules\Frontend\Http\Controllers\WEB\InstituteImageSettingController;
use Modules\Frontend\Http\Controllers\WEB\MobileAppSectionController;
use Modules\Frontend\Http\Controllers\WEB\PageController;
use Modules\Frontend\Http\Controllers\WEB\PolicyController;
use Modules\Frontend\Http\Controllers\WEB\ReadyToJoinUsController;
use Modules\Frontend\Http\Controllers\WEB\TestimonialsController;
use Modules\Frontend\Http\Controllers\WEB\WhyChooseUsController;

// Fees Management
Route::group(['middleware' => ['auth']], function () {
    Route::resource('about-us', AboutUsController::class);
    Route::resource('pages', PageController::class);
    Route::resource('banners', BannerController::class);
    Route::resource('faqs', FaqQuestionController::class);
    Route::resource('gallery-images', AcademicImageController::class);
    Route::resource('mobile-app-sections', MobileAppSectionController::class);
    Route::resource('why-choose-us', WhyChooseUsController::class);
    Route::resource('policies', PolicyController::class);
    Route::resource('ready-to-join-us', ReadyToJoinUsController::class);
    Route::resource('testimonials', TestimonialsController::class);
    Route::resource('institute-image-settings', InstituteImageSettingController::class);
    Route::post('admission-forms/{id}/approve', [AdmissionFormController::class, 'approve'])->name('admission-forms.approve');
    Route::post('admission-forms/{id}/reject', [AdmissionFormController::class, 'reject'])->name('admission-forms.reject');
    Route::resource('admission-forms', AdmissionFormController::class);
});

Route::get('academic-images', [AdmissionFormFrontendController::class, 'academicImages'])->name('academic-images');
Route::get('/result/print/{id}', [AdmissionFormFrontendController::class, 'printResult'])->name('result.pdf');
Route::get('result-page', [AdmissionFormFrontendController::class, 'resultPage'])->name('result-page');
Route::get('admission-form-page', [AdmissionFormFrontendController::class, 'index'])->name('admission-form-page');
Route::post('admission-form-submit', [AdmissionFormFrontendController::class, 'store'])->name('admission-form-submit');

// Frontend Page
Route::get('/event-details/{id}', [FrontendController::class, 'eventDetails'])->name('event-details');
Route::get('privacy-policy', [FrontendController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('terms-conditions', [FrontendController::class, 'termsConditions'])->name('terms-conditions');
Route::get('refund-policy', [FrontendController::class, 'refundPolicy'])->name('refund-policy');
Route::get('cookies-policy', [FrontendController::class, 'cookiesPolicy'])->name('cookies-policy');
Route::get('legal', [FrontendController::class, 'legal'])->name('legal');

Route::get('contact', [FrontendController::class, 'contact'])->name('contact');
Route::post('contact-submit', [FrontendController::class, 'contactSubmit'])->name('contact.submit');

Route::get('onboarding', [FrontendController::class, 'onboarding'])->name('onboarding');
Route::post('onboarding-submit', [FrontendController::class, 'onboardingSubmit'])->name('onboarding.submit');
