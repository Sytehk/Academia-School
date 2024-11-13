<?php

use Illuminate\Support\Facades\Route;
use Modules\Ai\Http\Controllers\WEB\AiChatController;
use Modules\Ai\Http\Controllers\WEB\AiContentWriterController;
use Modules\Ai\Http\Controllers\WEB\AiInsightsController;
use Modules\Ai\Http\Controllers\WEB\AiSettingsController;

Route::group(['middleware' => ['auth'], 'prefix' => 'ai'], function () {
    Route::get('chat', [AiChatController::class, 'index'])->name('ai.chat.index');
    Route::post('chat/send', [AiChatController::class, 'send'])->name('ai.chat.send');

    Route::get('writer', [AiContentWriterController::class, 'index'])->name('ai.writer.index');
    Route::post('writer/generate', [AiContentWriterController::class, 'generate'])->name('ai.writer.generate');

    Route::get('insights', [AiInsightsController::class, 'index'])->name('ai.insights.index');
    Route::post('insights/generate', [AiInsightsController::class, 'generate'])->name('ai.insights.generate');

    Route::get('settings', [AiSettingsController::class, 'index'])->name('ai.settings.index');
    Route::post('settings', [AiSettingsController::class, 'update'])->name('ai.settings.update');
    Route::post('settings/test', [AiSettingsController::class, 'testConnection'])->name('ai.settings.test');
});
