<?php

use Illuminate\Support\Facades\Route;
use Modules\Examination\Http\Controllers\WEB\AssessmentDomainController;
use Modules\Examination\Http\Controllers\WEB\ExamMarkInputController;
use Modules\Examination\Http\Controllers\WEB\ExamResultReportController;
use Modules\Examination\Http\Controllers\WEB\GrandFinalResultController;
use Modules\Examination\Http\Controllers\WEB\OnlineExamController;
use Modules\Examination\Http\Controllers\WEB\RemarksConfigController;
use Modules\Examination\Http\Controllers\WEB\ResultCardSettingController;
use Modules\Examination\Http\Controllers\WEB\SemesterExamSettingController;
use Modules\Examination\Http\Controllers\WEB\StudentOnlineExamController;

Route::group(['middleware' => ['auth']], function () {
    // Exam-Module
    Route::resource('remarks-config', RemarksConfigController::class);
    Route::get('grand-final-result', [GrandFinalResultController::class, 'index'])->name('grand-final-result.index');
    Route::post('grand-final-result/search', [GrandFinalResultController::class, 'search'])->name('grand-final-result.search');
    Route::post('grand-final-result/preview', [GrandFinalResultController::class, 'preview'])->name('grand-final-result.preview');
    Route::post('grand-final-result/pdf', [GrandFinalResultController::class, 'downloadPdf'])->name('grand-final-result.pdf');
    Route::get('semester-exam-settings-exam-startup', [SemesterExamSettingController::class, 'examStartUp']);
    Route::post('exam-code-store', [SemesterExamSettingController::class, 'examCodeStore'])->name('exam-code-store');
    Route::post('exam-code-update', [SemesterExamSettingController::class, 'examCodeUpdate'])->name('exam-code-update');
    Route::post('exam-grade-store', [SemesterExamSettingController::class, 'examGradeStore'])->name('exam-grade-store');
    Route::post('exam-grade-update', [SemesterExamSettingController::class, 'examGradeUpdate'])->name('exam-grade-update');
    Route::post('exam-assign-store', [SemesterExamSettingController::class, 'examAssignStore'])->name('exam-assign-store');
    Route::get('semester-exam-settings-mark-config', [SemesterExamSettingController::class, 'markConfig']);
    Route::post('general-exam-store', [SemesterExamSettingController::class, 'generalExamStore'])->name('general-exam-store');
    Route::post('grand-final-exam-store', [SemesterExamSettingController::class, 'grandFinalExamStore'])->name('grand-final-exam-store');
    Route::get('mark-input-section-wise', [SemesterExamSettingController::class, 'markInputSectionWise'])->name('mark-input-section-wise');
    Route::get('mark-input-section-wise-class/{class_id}', [SemesterExamSettingController::class, 'markInputSectionWiseClass'])->name('mark-input-section-wise-class');
    Route::get('mark-input-subjects/{class_id}', [SemesterExamSettingController::class, 'markInputSubjects'])->name('mark-input-subjects');
    Route::post('mark-store-section-wise', [SemesterExamSettingController::class, 'markStoreSectionWise'])->name('mark-store-section-wise');
    Route::get('excel-file-download', [ExamMarkInputController::class, 'excelFileDownload'])->name('excel-file-download');
    Route::get('mark-sheet-export', [ExamMarkInputController::class, 'export'])->name('mark-sheet-export');
    Route::post('mark-sheet-import', [ExamMarkInputController::class, 'import'])->name('mark-sheet-import');

    Route::get('exam-result-view', [ExamResultReportController::class, 'examResultView'])->name('exam-result-view');
    Route::post('exam-result-send-notifications', [ExamResultReportController::class, 'sendNotifications'])->name('exam-result-send-notifications');
    Route::get('exam-result-card', [ExamResultReportController::class, 'singleCard'])->name('exam-result-card');
    Route::get('exam-result-card-pdf', [ExamResultReportController::class, 'singleCardPdf'])->name('exam-result-card-pdf');
    Route::get('student-mark-view', [ExamResultReportController::class, 'studentMarkView'])->name('student-mark-view');
    Route::get('student-mark-pdf', [ExamResultReportController::class, 'studentMarkPdf'])->name('student-mark-pdf');
    Route::get('annual-result-card', [ExamResultReportController::class, 'annualCard'])->name('annual-result-card');
    Route::get('annual-result-card-pdf', [ExamResultReportController::class, 'annualCardPdf'])->name('annual-result-card-pdf');
    Route::get('tabulation-sheet', [ExamResultReportController::class, 'tabulation'])->name('tabulation-sheet');
    Route::get('tabulation-sheet-print', [ExamResultReportController::class, 'tabulationPrint'])->name('tabulation-sheet-print');
    Route::get('tabulation-sheet-pdf', [ExamResultReportController::class, 'tabulationPdf'])->name('tabulation-sheet-pdf');
    Route::get('merit-list-sheet', [ExamResultReportController::class, 'meritList'])->name('merit-list-sheet');
    Route::get('merit-list-sheet-print', [ExamResultReportController::class, 'meritListPrint'])->name('merit-list-sheet-print');
    Route::get('merit-list-sheet-pdf', [ExamResultReportController::class, 'meritListPdf'])->name('merit-list-sheet-pdf');
    Route::get('merit-list-sheet-excel', [ExamResultReportController::class, 'meritListExcel'])->name('merit-list-sheet-excel');
    Route::get('empty-mark-sheet', [ExamResultReportController::class, 'emptyMarkSheet'])->name('empty-mark-sheet');
    Route::get('empty-mark-sheet-print', [ExamResultReportController::class, 'emptyMarkSheetPrint'])->name('empty-mark-sheet-print');
    Route::get('result-card-settings', [ResultCardSettingController::class, 'edit'])->name('result-card-settings.edit');
    Route::post('result-card-settings', [ResultCardSettingController::class, 'update'])->name('result-card-settings.update');

    Route::get('assessment-domains', [AssessmentDomainController::class, 'index'])->name('assessment-domains.index');
    Route::post('assessment-domains', [AssessmentDomainController::class, 'store'])->name('assessment-domains.store');
    Route::post('assessment-domains/{domain}/items', [AssessmentDomainController::class, 'storeItem'])->name('assessment-domains.items.store');
    Route::delete('assessment-domains/{id}', [AssessmentDomainController::class, 'destroy'])->name('assessment-domains.destroy');
    Route::get('assessment-entry', [AssessmentDomainController::class, 'entryForm'])->name('assessment-entry.index');
    Route::post('assessment-entry', [AssessmentDomainController::class, 'storeEntry'])->name('assessment-entry.store');

    Route::resource('online-exams', OnlineExamController::class)->except(['show']);
    Route::post('online-exams/{id}/questions', [OnlineExamController::class, 'storeQuestion'])->name('online-exams.questions.store');
    Route::delete('online-exam-questions/{id}', [OnlineExamController::class, 'destroyQuestion'])->name('online-exams.questions.destroy');
    Route::get('online-exams/{id}/results', [OnlineExamController::class, 'results'])->name('online-exams.results');
    Route::get('online-exams/{id}/results-print', [OnlineExamController::class, 'resultsPrint'])->name('online-exams.results-print');
    Route::get('online-exams/{id}/transfer', [OnlineExamController::class, 'transferPreview'])->name('online-exams.transfer');
    Route::post('online-exams/{id}/transfer', [OnlineExamController::class, 'transfer'])->name('online-exams.transfer.store');
    Route::get('online-exams/{id}/transfer-print', [OnlineExamController::class, 'transferPreviewPrint'])->name('online-exams.transfer-print');
});

Route::group(['middleware' => ['student']], function () {
    Route::get('student/online-exams', [StudentOnlineExamController::class, 'index'])->name('student.online-exams.index');
    Route::post('student/online-exams/{id}/start', [StudentOnlineExamController::class, 'start'])->name('student.online-exams.start');
    Route::get('student/online-exams/take/{attempt}', [StudentOnlineExamController::class, 'take'])->name('student.online-exams.take');
    Route::post('student/online-exams/take/{attempt}/save', [StudentOnlineExamController::class, 'save'])->name('student.online-exams.save');
    Route::post('student/online-exams/take/{attempt}/submit', [StudentOnlineExamController::class, 'submit'])->name('student.online-exams.submit');
    Route::get('student/online-exams/result/{attempt}', [StudentOnlineExamController::class, 'result'])->name('student.online-exams.result');
});
