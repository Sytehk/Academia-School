<?php

use Illuminate\Support\Facades\Route;
use Modules\Academic\Http\Controllers\API\ExamController;
use Modules\Examination\Http\Controllers\API\AcademicRecordController;
use Modules\Examination\Http\Controllers\API\ExamMarkInputController;
use Modules\Examination\Http\Controllers\API\ExamResultApiController;
use Modules\Examination\Http\Controllers\API\MarkSheetController;
use Modules\Examination\Http\Controllers\API\OnlineExamController;
use Modules\Examination\Http\Controllers\API\PortalResultController;
use Modules\Examination\Http\Controllers\API\RemarksConfigController;
use Modules\Examination\Http\Controllers\API\ReportCardController;
use Modules\Examination\Http\Controllers\API\ResultWorkflowController;
use Modules\Examination\Http\Controllers\API\SemesterExamSettingController;
use Modules\Examination\Http\Controllers\API\StudentOnlineExamController;
use Modules\Examination\Http\Controllers\API\TranscriptController;

/*
| Every group carries an `access:` gate mirroring the permission its web
| counterpart checks. Portal users (Student/Teacher/Parent) never reach these
| routes at all — RejectPortalApiUser stops them — so the gates separate the
| back-office roles from one another (an accountant has no business storing marks).
*/

// Read-only lookups shared by every exam screen (and exam routine / attendance / admit card).
$anyExam = 'access:exam_module_exam_start_up,exam_module_mark_config,exam_module_mark_input,exam_module_exam_result,'
    .'exam_module_print_result,exam_module_view_broad_sheet,exam_module_manage_online_exams,academic_configuration_exams,'
    .'routine_management_exam_routine,routine_management_admit_seat_plan,student_attendance_exam_attendance_index';

Route::middleware('auth:api')->group(function () use ($anyExam) {
    Route::middleware($anyExam)->group(function () {
        Route::get('short-code', [SemesterExamSettingController::class, 'shortCode']);
        Route::get('grades', [SemesterExamSettingController::class, 'grades']);
        Route::get('merit-process-type', [SemesterExamSettingController::class, 'meritProcessType']);
        Route::get('class-exam', [SemesterExamSettingController::class, 'classExam']);
        Route::get('exam-code', [SemesterExamSettingController::class, 'examCode']);
        Route::get('exam-grade', [SemesterExamSettingController::class, 'examGrade']);
        Route::apiResource('exams', ExamController::class)->only(['index', 'show']);
    });

    Route::middleware('access:academic_configuration_exams,exam_module_exam_start_up')->group(function () {
        Route::apiResource('exams', ExamController::class)->only(['store', 'update', 'destroy']);
    });

    Route::middleware('access:exam_module_exam_start_up')->group(function () {
        Route::post('exam-code-store', [SemesterExamSettingController::class, 'examCodeStore']);
        Route::post('exam-code-update', [SemesterExamSettingController::class, 'examCodeUpdate']);
        Route::post('exam-grade-store', [SemesterExamSettingController::class, 'examGradeStore']);
        Route::post('exam-grade-update', [SemesterExamSettingController::class, 'examGradeUpdate']);
    });

    Route::middleware('access:exam_module_remarks_config,exam_module_exam_start_up')->group(function () {
        Route::apiResource('remarks-config', RemarksConfigController::class);
    });

    Route::middleware('access:exam_module_mark_config,exam_module_exam_start_up')->group(function () {
        Route::post('exam-assign-store', [ExamMarkInputController::class, 'examAssignStore']);
        Route::get('semester-exam-settings-mark-config', [ExamMarkInputController::class, 'markConfig']);
        Route::get('grand-final-mark-percentage/{class_id}', [ExamMarkInputController::class, 'grandFinalMarkPercentage']);
        Route::post('general-exam-store', [ExamMarkInputController::class, 'generalExamStore']);
        Route::post('grand-final-exam-store', [ExamMarkInputController::class, 'grandFinalExamStore']);
    });

    Route::middleware('access:exam_module_mark_input')->group(function () {
        Route::get('mark-input-section-wise-class/{class_id}', [ExamMarkInputController::class, 'markInputSectionWiseClass']);
        Route::post('mark-store-section-wise', [ExamMarkInputController::class, 'markStoreSectionWise']);
        Route::get('mark-sheet-export', [MarkSheetController::class, 'export']);
        Route::post('mark-sheet-import', [MarkSheetController::class, 'import']);
    });

    Route::middleware('access:exam_module_exam_result,exam_module_print_result')->group(function () {
        Route::get('exam-results', [ExamMarkInputController::class, 'examResult']);
        Route::get('exam-results-export', [MarkSheetController::class, 'resultExport']);
        Route::get('exam-result-card', [ExamResultApiController::class, 'resultCard']);
        Route::get('annual-result-card', [ExamResultApiController::class, 'annualResultCard']);
    });

    Route::middleware('access:exam_module_view_broad_sheet,exam_module_print_broad_sheet,exam_module_exam_result')->group(function () {
        Route::get('tabulation-sheet', [ExamResultApiController::class, 'tabulation']);
    });

    Route::middleware('access:exam_module_mark_input,exam_module_print_result')->group(function () {
        Route::get('empty-mark-sheet', [ExamResultApiController::class, 'emptyMarkSheet']);
    });

    Route::middleware('access:exam_module_exam_result,exam_module_exam_start_up')->group(function () {
        Route::get('result-card-settings', [ExamResultApiController::class, 'resultCardSettings']);
        Route::post('result-card-settings', [ExamResultApiController::class, 'updateResultCardSettings']);
    });

    Route::middleware('access:exam_module_manage_assessment_domains,exam_module_exam_start_up')->group(function () {
        Route::get('assessment-domains', [ExamResultApiController::class, 'assessmentDomains']);
        Route::post('assessment-domains', [ExamResultApiController::class, 'storeAssessmentDomain']);
        Route::post('assessment-items', [ExamResultApiController::class, 'storeAssessmentItem']);
    });

    Route::middleware('access:exam_module_manage_assessment_domains,exam_module_mark_input')->group(function () {
        Route::post('student-assessments', [ExamResultApiController::class, 'storeStudentAssessments']);
    });

    // ── Result workflow, comments, term details, statistics, report cards ─
    Route::middleware('access:exam_module_exam_result,exam_module_approve_result,exam_module_publish_result,exam_module_mark_input,exam_module_result_comments')->group(function () {
        Route::get('result-publications', [ResultWorkflowController::class, 'index']);
        Route::get('result-publications/logs', [ResultWorkflowController::class, 'logs']);
        // Each action re-checks its own permission (ResultPublicationService::ACTION_PERMISSIONS).
        Route::post('result-publications/{action}', [ResultWorkflowController::class, 'transition'])
            ->whereIn('action', ['submit', 'approve', 'reject', 'publish', 'unpublish', 'lock', 'unlock']);
    });

    Route::middleware('access:exam_module_result_comments,exam_module_principal_comment')->group(function () {
        Route::get('result-comments', [ResultWorkflowController::class, 'comments']);
        Route::post('result-comments', [ResultWorkflowController::class, 'storeComments']);
    });

    Route::middleware('access:exam_module_exam_start_up,exam_module_exam_result')->group(function () {
        Route::get('exam-term-details/{examId}', [ResultWorkflowController::class, 'showTerm']);
        Route::put('exam-term-details/{examId}', [ResultWorkflowController::class, 'updateTerm']);
    });

    Route::middleware('access:exam_module_exam_result,exam_module_print_result,exam_module_view_broad_sheet')->group(function () {
        Route::get('class-result-statistics', [ResultWorkflowController::class, 'statistics']);
        Route::get('exam-result-card-pdf', [ReportCardController::class, 'studentPdf']);
        Route::get('class-result-cards-pdf', [ReportCardController::class, 'classPdf']);
    });

    Route::middleware('access:academic_student_records,exam_module_exam_result')->group(function () {
        Route::get('students/{studentId}/academic-record', [AcademicRecordController::class, 'show'])->whereNumber('studentId');
    });

    Route::middleware('access:academic_transcript_issue,academic_student_records')->group(function () {
        Route::post('students/{studentId}/transcript', [TranscriptController::class, 'issue'])->whereNumber('studentId');
        Route::get('transcripts/{issueId}/pdf', [TranscriptController::class, 'pdf'])->whereNumber('issueId')->name('transcripts.pdf');
    });

    // ── Online exams (CBT) ───────────────────────────────────────────────
    Route::middleware('access:exam_module_manage_online_exams,exam_module_exam_start_up')->group(function () {
        Route::get('online-exams', [OnlineExamController::class, 'index']);
        Route::post('online-exams', [OnlineExamController::class, 'store']);
        Route::get('online-exams/{id}', [OnlineExamController::class, 'show'])->whereNumber('id');
        Route::put('online-exams/{id}', [OnlineExamController::class, 'update'])->whereNumber('id');
        Route::delete('online-exams/{id}', [OnlineExamController::class, 'destroy'])->whereNumber('id');
        Route::post('online-exams/{id}/publish', [OnlineExamController::class, 'publish']);
        Route::post('online-exams/{id}/unpublish', [OnlineExamController::class, 'unpublish']);
        Route::post('online-exams/{id}/close', [OnlineExamController::class, 'close']);
        Route::get('online-exams/{id}/pools', [OnlineExamController::class, 'pools']);
        Route::post('online-exams/{id}/pools', [OnlineExamController::class, 'storePool']);
        Route::put('online-exam-pools/{poolId}', [OnlineExamController::class, 'updatePool']);
        Route::delete('online-exam-pools/{poolId}', [OnlineExamController::class, 'destroyPool']);
        Route::get('online-exams/{id}/results', [OnlineExamController::class, 'results']);
        Route::get('online-exams/{id}/attempts/{attemptId}', [OnlineExamController::class, 'attempt']);
    });

    Route::middleware('access:exam_module_manage_online_questions,exam_module_exam_start_up')->group(function () {
        Route::post('online-exams/{id}/questions', [OnlineExamController::class, 'storeQuestion']);
        Route::post('online-exams/{id}/questions/from-bank', [OnlineExamController::class, 'addFromBank']);
        Route::post('online-exams/{id}/questions/from-bank-criteria', [OnlineExamController::class, 'addFromBankByCriteria']);
        Route::put('online-exam-questions/{questionId}', [OnlineExamController::class, 'updateQuestion']);
        Route::delete('online-exam-questions/{questionId}', [OnlineExamController::class, 'destroyQuestion']);
    });

    Route::middleware('access:exam_module_grade_online_answers,exam_module_exam_start_up')->group(function () {
        Route::get('online-exams/{id}/grading-queue', [OnlineExamController::class, 'gradingQueue']);
        Route::post('online-exam-attempts/{attemptId}/grade', [OnlineExamController::class, 'grade']);
    });

    Route::middleware('access:exam_module_transfer_online_marks,exam_module_mark_input')->group(function () {
        Route::get('online-exams/{id}/transfer-preview', [OnlineExamController::class, 'transferPreview']);
        Route::post('online-exams/{id}/transfer', [OnlineExamController::class, 'transfer']);
    });
});

// Teacher portal: the same CBT management, limited to the teacher's own subjects.
Route::prefix('teacher')->middleware(['auth:api', 'teacher'])->group(function () {
    Route::get('result-comments', [ResultWorkflowController::class, 'comments']);
    Route::post('result-comments', [ResultWorkflowController::class, 'storeComments']);
    Route::get('online-exams', [OnlineExamController::class, 'index']);
    Route::post('online-exams', [OnlineExamController::class, 'store']);
    Route::get('online-exams/{id}', [OnlineExamController::class, 'show'])->whereNumber('id');
    Route::put('online-exams/{id}', [OnlineExamController::class, 'update'])->whereNumber('id');
    Route::delete('online-exams/{id}', [OnlineExamController::class, 'destroy'])->whereNumber('id');
    Route::post('online-exams/{id}/publish', [OnlineExamController::class, 'publish']);
    Route::post('online-exams/{id}/unpublish', [OnlineExamController::class, 'unpublish']);
    Route::post('online-exams/{id}/close', [OnlineExamController::class, 'close']);
    Route::get('online-exams/{id}/pools', [OnlineExamController::class, 'pools']);
    Route::post('online-exams/{id}/pools', [OnlineExamController::class, 'storePool']);
    Route::put('online-exam-pools/{poolId}', [OnlineExamController::class, 'updatePool']);
    Route::delete('online-exam-pools/{poolId}', [OnlineExamController::class, 'destroyPool']);
    Route::get('online-exams/{id}/results', [OnlineExamController::class, 'results']);
    Route::get('online-exams/{id}/attempts/{attemptId}', [OnlineExamController::class, 'attempt']);
    Route::post('online-exams/{id}/questions', [OnlineExamController::class, 'storeQuestion']);
    Route::post('online-exams/{id}/questions/from-bank', [OnlineExamController::class, 'addFromBank']);
    Route::post('online-exams/{id}/questions/from-bank-criteria', [OnlineExamController::class, 'addFromBankByCriteria']);
    Route::put('online-exam-questions/{questionId}', [OnlineExamController::class, 'updateQuestion']);
    Route::delete('online-exam-questions/{questionId}', [OnlineExamController::class, 'destroyQuestion']);
    Route::get('online-exams/{id}/grading-queue', [OnlineExamController::class, 'gradingQueue']);
    Route::post('online-exam-attempts/{attemptId}/grade', [OnlineExamController::class, 'grade']);
});

// Student portal CBT.
Route::prefix('student')->middleware(['auth:api', 'student'])->group(function () {
    Route::get('academic-record', [AcademicRecordController::class, 'mine']);
    Route::get('results', [PortalResultController::class, 'index']);
    Route::get('results/{examId}', [PortalResultController::class, 'show'])->whereNumber('examId');
    Route::get('results/{examId}/pdf', [PortalResultController::class, 'pdf'])->whereNumber('examId');
    Route::get('online-exams', [StudentOnlineExamController::class, 'index']);
    Route::post('online-exams/{id}/start', [StudentOnlineExamController::class, 'start']);
    Route::get('online-exam-attempts', [StudentOnlineExamController::class, 'attempts']);
    Route::get('online-exam-attempts/{attempt}', [StudentOnlineExamController::class, 'show']);
    Route::post('online-exam-attempts/{attempt}/save', [StudentOnlineExamController::class, 'save']);
    Route::post('online-exam-attempts/{attempt}/submit', [StudentOnlineExamController::class, 'submit']);
    Route::get('online-exam-attempts/{attempt}/result', [StudentOnlineExamController::class, 'result']);
});

// Parent portal: the selected child's published results.
Route::prefix('parent/my-children')->middleware(['auth:api', 'parent'])->group(function () {
    Route::get('academic-record', [AcademicRecordController::class, 'mine']);
    Route::get('results', [PortalResultController::class, 'index']);
    Route::get('results/{examId}', [PortalResultController::class, 'show'])->whereNumber('examId');
    Route::get('results/{examId}/pdf', [PortalResultController::class, 'pdf'])->whereNumber('examId');
});

Route::get('transcript-verify/{code}', [TranscriptController::class, 'verify']);
