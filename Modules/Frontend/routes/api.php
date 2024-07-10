<?php

use Illuminate\Support\Facades\Route;
use Modules\Frontend\Http\Controllers\API\AboutUsController;
use Modules\Frontend\Http\Controllers\API\AcademicImageController;
use Modules\Frontend\Http\Controllers\API\AchievementController;
use Modules\Frontend\Http\Controllers\API\AdmissionFormController;
use Modules\Frontend\Http\Controllers\API\BannerController;
use Modules\Frontend\Http\Controllers\API\BenefitController;
use Modules\Frontend\Http\Controllers\API\FaqQuestionController;
use Modules\Frontend\Http\Controllers\API\FrontendController;
use Modules\Frontend\Http\Controllers\API\HomeSectionController;
use Modules\Frontend\Http\Controllers\API\MobileAppSectionController;
use Modules\Frontend\Http\Controllers\API\PolicyController;
use Modules\Frontend\Http\Controllers\API\ReadyToJoinUsController;
use Modules\Frontend\Http\Controllers\API\SAASPolicyController;
use Modules\Frontend\Http\Controllers\API\TestimonialsController;
use Modules\Frontend\Http\Controllers\API\WhyChooseUsController;

// FrontendController Api Routes Start
Route::prefix('/frontend')->controller(FrontendController::class)->group(function () {
    Route::get('bootstrap', 'bootstrap');
    Route::get('banners', 'banners');
    Route::get('about-us', 'aboutUs');
    Route::get('why-choose-us', 'whyChooseUs');
    Route::get('ready-to-join-us', 'readyToJoinUs');
    Route::get('teachers', 'teachers');
    Route::get('staffs', 'staffs');
    Route::get('events', 'events');
    Route::get('/event-details/{id}', 'eventDetails');
    Route::get('notices', 'notices');
    Route::get('faq', 'faq');
    Route::get('terms-conditions', 'termConditions');
    Route::get('privacy-policy', 'privacyPolicy');
    Route::get('cookie-policy', 'cookiePolicy');
    Route::get('refund-policy', 'refundPolicy');
    Route::get('legal', 'legal');
    Route::get('gallery-images/{id}', 'academicImageShow');
    Route::get('testimonials', 'testimonials');
    Route::get('mobile-app-sections', 'mobileAppSections');
    Route::get('settings', 'settings');
    Route::get('themes', 'themes');
    Route::get('default-theme', 'defaultTheme');
    Route::get('gallery-images', 'academicImages');
    Route::get('institute-image-saas-settings', 'instituteImageSAASSetting');
    Route::get('institute-image-settings', 'instituteImageSetting');
    Route::get('benefits', 'benefits');
    Route::get('achievements', 'achievements');
    Route::get('home-sections', 'homeSections');
    Route::get('pages', 'pages');
    Route::get('page/{slug}', 'pageShow');
    Route::post('contact-us', 'contactUs');
    Route::post('onboarding', 'onboarding');
});

Route::prefix('/frontend')->controller(FrontendController::class)->group(function () {
    Route::get('get-classes', 'getClasses');
    Route::get('get-class-exam', 'getClassExam');
});

Route::middleware('auth:api')->group(function () {
    Route::apiResource('banners', BannerController::class);
    Route::apiResource('faqs', FaqQuestionController::class);
    Route::apiResource('about-us', AboutUsController::class);
    Route::apiResource('policies', PolicyController::class);
    Route::apiResource('testimonials', TestimonialsController::class);
    Route::apiResource('why-choose-us', WhyChooseUsController::class);
    Route::apiResource('mobile-app-sections', MobileAppSectionController::class);
    Route::apiResource('ready-to-join-us', ReadyToJoinUsController::class);
    Route::apiResource('gallery-images', AcademicImageController::class);
    Route::apiResource('benefits', BenefitController::class);
    Route::apiResource('achievements', AchievementController::class);
    Route::apiResource('saas-policies', SAASPolicyController::class);
    Route::apiResource('home-sections', HomeSectionController::class);
    Route::apiResource('admission-forms', AdmissionFormController::class);
});

Route::post('frontend/admission-forms', [AdmissionFormController::class, 'store']);
Route::post('frontend/exam-result', [AdmissionFormController::class, 'examResult']);
