<?php

use Illuminate\Support\Facades\Route;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankBoardController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankChapterController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankClassController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankDifficultyLevelController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankGroupController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankLevelController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankSessionController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankSourceController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankSubjectController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankSubSourceController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankTagController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankTestController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankTopicController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankTypeController;
use Modules\QuestionBank\Http\Controllers\API\QuestionBankYearController;
use Modules\QuestionBank\Http\Controllers\API\QuestionCategoryController;
use Modules\QuestionBank\Http\Controllers\API\QuestionController;
use Modules\QuestionBank\Http\Controllers\API\QuestionSpreadsheetController;
use Modules\QuestionBank\Http\Controllers\API\QuizController;
use Modules\QuestionBank\Http\Controllers\API\QuizTestController;

$resources = [
    'question-bank-classes' => QuestionBankClassController::class,
    'question-bank-groups' => QuestionBankGroupController::class,
    'question-bank-subjects' => QuestionBankSubjectController::class,
    'question-bank-chapters' => QuestionBankChapterController::class,
    'question-bank-topics' => QuestionBankTopicController::class,
    'question-bank-types' => QuestionBankTypeController::class,
    'question-bank-sources' => QuestionBankSourceController::class,
    'question-bank-sub-sources' => QuestionBankSubSourceController::class,
    'question-bank-years' => QuestionBankYearController::class,
    'question-bank-boards' => QuestionBankBoardController::class,
    'question-bank-tags' => QuestionBankTagController::class,
    'question-bank-levels' => QuestionBankLevelController::class,
    'question-bank-sessions' => QuestionBankSessionController::class,
    'question-bank-difficulty-levels' => QuestionBankDifficultyLevelController::class,
    'question-bank-tests' => QuestionBankTestController::class,
    'question-categories' => QuestionCategoryController::class,
    'questions' => QuestionController::class,
    'quizzes' => QuizController::class,
    'quiz-test' => QuizTestController::class,
];

Route::middleware(['auth:api'])->group(function () use ($resources) {
    // Browsing the bank: bank managers, and online-exam authors picking questions.
    Route::middleware('access:question_bank_manage,exam_module_manage_online_questions')->group(function () use ($resources) {
        Route::get('question-categories-wise-subjects', [QuestionCategoryController::class, 'getSubjectsByCategory']);
        foreach ($resources as $uri => $controller) {
            Route::apiResource($uri, $controller)->only(['index', 'show']);
        }
    });

    Route::middleware('access:question_bank_manage,question_bank_import')->group(function () {
        Route::get('questions-export', [QuestionSpreadsheetController::class, 'export']);
        Route::get('questions-import-template', [QuestionSpreadsheetController::class, 'template']);
    });

    Route::middleware('access:question_bank_import')->group(function () {
        Route::post('questions-import', [QuestionSpreadsheetController::class, 'import']);
    });

    Route::middleware('access:question_bank_manage')->group(function () use ($resources) {
        Route::put('/quizzes/{id}/questions', [QuizController::class, 'updateQuestions']);
        foreach ($resources as $uri => $controller) {
            Route::apiResource($uri, $controller)->only(['store', 'update', 'destroy']);
        }
    });
});
