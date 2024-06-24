<?php

use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;
use Modules\Academic\Http\Controllers\WEB\AcademicYearsController;
use Modules\Academic\Http\Controllers\WEB\AssignmentController;
use Modules\Academic\Http\Controllers\WEB\AssignShiftController;
use Modules\Academic\Http\Controllers\WEB\AssignSubjectController;
use Modules\Academic\Http\Controllers\WEB\AttendanceController;
use Modules\Academic\Http\Controllers\WEB\ClassAssignController;
use Modules\Academic\Http\Controllers\WEB\ClassController;
use Modules\Academic\Http\Controllers\WEB\ClassRoutineController;
use Modules\Academic\Http\Controllers\WEB\ContactController;
use Modules\Academic\Http\Controllers\WEB\DepartmentsController;
use Modules\Academic\Http\Controllers\WEB\EventController;
use Modules\Academic\Http\Controllers\WEB\ExamAttendanceController;
use Modules\Academic\Http\Controllers\WEB\ExamController;
use Modules\Academic\Http\Controllers\WEB\ExamEssentialsController;
use Modules\Academic\Http\Controllers\WEB\GoogleMeetController;
use Modules\Academic\Http\Controllers\WEB\GoogleMeetSettingController;
use Modules\Academic\Http\Controllers\WEB\NoticeController;
use Modules\Academic\Http\Controllers\WEB\OptionalSubjectConfigController;
use Modules\Academic\Http\Controllers\WEB\PeriodController;
use Modules\Academic\Http\Controllers\WEB\PicklistController;
use Modules\Academic\Http\Controllers\WEB\QrAttendanceController;
use Modules\Academic\Http\Controllers\WEB\SectionController;
use Modules\Academic\Http\Controllers\WEB\ShiftController;
use Modules\Academic\Http\Controllers\WEB\SignatureController;
use Modules\Academic\Http\Controllers\WEB\StaffController;
use Modules\Academic\Http\Controllers\WEB\StudentBulkUploadController;
use Modules\Academic\Http\Controllers\WEB\StudentCategoriesController;
use Modules\Academic\Http\Controllers\WEB\StudentController;
use Modules\Academic\Http\Controllers\WEB\StudentGroupController;
use Modules\Academic\Http\Controllers\WEB\StudentMigrationController;
use Modules\Academic\Http\Controllers\WEB\StudentOptionalSubjectController;
use Modules\Academic\Http\Controllers\WEB\SubjectConfigController;
use Modules\Academic\Http\Controllers\WEB\SubjectController;
use Modules\Academic\Http\Controllers\WEB\SyllabusController;
use Modules\Academic\Http\Controllers\WEB\TeacherBulkUploadController;
use Modules\Academic\Http\Controllers\WEB\TeacherController;
use Modules\Academic\Http\Controllers\WEB\WhatsAppLogController;
use Modules\Academic\Http\Controllers\WEB\WhatsAppSettingController;
use Modules\Academic\Http\Controllers\WEB\WhatsAppTemplateController;
use Modules\Academic\Http\Controllers\WEB\ZoomController;

Route::group(['middleware' => ['auth']], function () {
    // ALL Resource Controller
    Route::resource('academic-years', AcademicYearsController::class);
    Route::resource('shift', ShiftController::class);
    Route::resource('class', ClassController::class);
    Route::resource('exams', ExamController::class);
    Route::get('sections-class/{class_id}', [SectionController::class, 'index'])->name('sections.class-wise');
    Route::resource('sections', SectionController::class);
    Route::post('sections-section-group-wise', [SectionController::class, 'get_section2']);
    Route::get('groups-class-section-wise', [SectionController::class, 'classWiseGroups']);
    Route::post('class-wise-subjects', [SectionController::class, 'classWiseSubjects']);

    Route::resource('student-groups', StudentGroupController::class);
    Route::resource('periods', PeriodController::class);
    Route::get('subjects/class/{class_id}', [SubjectController::class, 'index'])->name('subjects.class-wise');
    Route::resource('subjects', SubjectController::class);
    Route::resource('departments', DepartmentsController::class);
    Route::get('picklists-type/{type}', [PicklistController::class, 'type'])->name('picklists.type');
    Route::resource('picklists', PicklistController::class);
    Route::resource('student-categories', StudentCategoriesController::class);
    Route::resource('signatures', SignatureController::class);

    // Subject Config Settings
    Route::resource('subject-config', SubjectConfigController::class)->only('index', 'create', 'store', 'destroy');
    Route::put('subject-config-update', [SubjectConfigController::class, 'updateConfig'])->name('subject-config.update');
    Route::get('optional-subject/get-subjects', [OptionalSubjectConfigController::class, 'getSubjects'])->name('optional-subject.getSubjects');

    Route::resource('optional-subject-config', OptionalSubjectConfigController::class);

    Route::get('student-optional/get-subjects', [StudentOptionalSubjectController::class, 'getStudentOptionalSubjects'])->name('student-optional.getSubjects');
    Route::resource('student-optional-subject', StudentOptionalSubjectController::class);

    // StaffController
    Route::resource('staffs', StaffController::class);
    Route::post('staffs/update-staff-status', [StaffController::class, 'updateStaffStatus'])->name('staffs.updateStatus');
    Route::post('staffs-multiple-delete', [StaffController::class, 'staffMultipleDelete'])->name('staffs-multiple.delete');

    // StudentController
    Route::resource('students', StudentController::class)->middleware('plan.limit:student');
    Route::get('students-status-enable/{id}', [StudentController::class, 'statusEnable'])->name('students.status-enable');
    Route::get('students-status-disable/{id}', [StudentController::class, 'statusDisable'])->name('students.status-disable');
    Route::post('students-multiple-delete', [StudentController::class, 'multipleDelete'])->name('students-multiple.delete');
    Route::get('at-a-glance', [StudentController::class, 'atAGlance'])->name('at-a-glance.index');
    Route::post('sections-section', [SectionController::class, 'get_section']);

    // StudentBulkUploadController Route
    Route::get('students-bulk-file-upload', [StudentBulkUploadController::class, 'index'])->name('students.bulk-file-upload');
    Route::post('students-file-upload/store', [StudentBulkUploadController::class, 'store'])->name('students.bulk-file-upload.store');
    Route::get('students-download-file-upload/store', [StudentBulkUploadController::class, 'downloadDemoFile'])->name('students.download.demo.file');

    // student migration
    Route::get('migrated-list', [StudentMigrationController::class, 'migratedList'])->name('migrated-list.index');
    Route::get('students-get_students-migration/{class_id}/{section}', [StudentMigrationController::class, 'get_student_for_migration']);
    // Only index/store are implemented — student_migrations rows are an append-only
    // audit log with no edit/show/destroy actions, so the rest of the resource
    // routes advertised 404/500 endpoints that never existed.
    Route::resource('student-migration', StudentMigrationController::class)->only(['index', 'store']);
    Route::get('student-migration-pushback', [StudentMigrationController::class, 'index'])->name('student-migration-pushback.index');
    Route::post('migrated-list', [StudentMigrationController::class, 'migratedList'])->name('migrated-list.search');

    // TeacherController
    Route::post('teachers/update-teacher-status', [TeacherController::class, 'updateTeacherStatus'])->name('teachers.updateStatus');
    Route::resource('teachers', TeacherController::class);
    Route::get('teacher.status-enable/{id}', [TeacherController::class, 'statusEnable'])->name('teacher.status-enable');
    Route::get('teacher.status-disable/{id}', [TeacherController::class, 'statusDisable'])->name('teacher.status-disable');
    Route::post('teachers-multiple-delete', [TeacherController::class, 'teacherMultipleDelete'])->name('teachers-multiple.delete');

    // TeacherBulkUploadController Route
    Route::get('teachers-bulk-file-upload', [TeacherBulkUploadController::class, 'index'])->name('teachers.bulk-file-upload');
    Route::post('teachers-file-upload/store', [TeacherBulkUploadController::class, 'store'])->name('teachers.bulk-file-upload.store');
    Route::get('teachers-download-file-upload/store', [TeacherBulkUploadController::class, 'downloadDemoFile'])->name('teachers.download.demo.file');

    // Attendance Controller
    Route::match(['get', 'post'], 'student-attendance', [AttendanceController::class, 'student_attendance'])->name('student_attendance_create');
    Route::post('student-attendance-save', [AttendanceController::class, 'student_attendance_save'])->name('student_attendance_save');
    Route::match(['get', 'post'], 'staffs-attendance', [AttendanceController::class, 'staff_attendance'])->name('staff_attendance.create');
    Route::post('staffs-attendance-save', [AttendanceController::class, 'staff_attendance_save'])->name('staff_attendance.save');
    Route::match(['get', 'post'], 'student_attendance/delete', [AttendanceController::class, 'studentAttendanceDelete'])->name('student_attendance_delete');
    Route::match(['get', 'post'], 'reports-student_attendance_date_to_date/{view?}', [AttendanceController::class, 'student_attendance_date_to_date'])->name('reports.student_attendance_date_to_date');
    Route::post('student-attendance-ratio-date-to-date-pdf-download', [AttendanceController::class, 'studentAttendanceRatioDateToDatePdfDownload'])->name('student-attendance-ratio-date-to-date-pdf-download');
    Route::match(['get', 'post'], 'absent-fine-report', [AttendanceController::class, 'absentFineReport'])->name('absent-fine-report');

    // Exam Attendance
    Route::match(['get', 'post'], 'exams-schedule/{type?}', [ExamAttendanceController::class, 'exam_schedule'])->name('exams.view_schedule');
    Route::match(['get', 'post'], 'exams-attendance', [ExamAttendanceController::class, 'exam_attendance'])->name('exams.store_exam_attendance');
    Route::post('exams-attendance-take', [ExamAttendanceController::class, 'store_exam_attendance'])->name('store_exam_attendance');

    // Notice Controller
    Route::resource('notices', NoticeController::class);

    // Event Controller
    Route::resource('events', EventController::class);

    // Assign Subject Route
    Route::post('assign-subjects-search', [AssignSubjectController::class, 'search'])->name('assign-subjects.search');
    Route::resource('assign-subjects', AssignSubjectController::class);

    // Assign Shift
    Route::resource('assign-shifts', AssignShiftController::class);

    // Assign Class
    Route::resource('assign-class', ClassAssignController::class);
    Route::resource('syllabus', SyllabusController::class);
    Route::resource('assignments', AssignmentController::class);
    Route::resource('exam-essentials', ExamEssentialsController::class);

    Route::get('contact-message/{id?}', [ContactController::class, 'index'])->name('contact-message');
    Route::get('contact-message-change-status/{id}', [ContactController::class, 'status'])->name('contact-message-change-status');

    // Class Routine
    Route::get('class_routines', [ClassRoutineController::class, 'index'])->name('class_routines.index');
    Route::get('class-routines-class/{class_id}', [ClassRoutineController::class, 'class'])->name('class_routines.by_class');
    Route::get('class-routines-manage/{class_id}/{section_id}', [ClassRoutineController::class, 'manage'])->name('class_routines.edit');
    Route::post('class-routines-store', [ClassRoutineController::class, 'store'])->name('class_routines.store');
    Route::get('class-routines-show/{class_id}/{section_id}', [ClassRoutineController::class, 'show'])->name('class_routines.show');

    // Exam Routine
    Route::match(['get', 'post'], 'exam-routines/{type?}', [ClassRoutineController::class, 'exam_schedule'])->name('exams.routines');
    Route::post('exam-routines-store', [ClassRoutineController::class, 'store_exam_schedule'])->name('exams.exam-routines.store');

    // Zoom API
    Route::resource('zoom', ZoomController::class);

    // WhatsApp Route
    Route::resource('whats-app-settings', WhatsAppSettingController::class);
    Route::resource('whats-app-templates', WhatsAppTemplateController::class);
    Route::post('whats-app-settings/test-connection', [WhatsAppLogController::class, 'testConnection'])->name('whats-app-settings.test-connection');
    Route::get('whats-app-logs', [WhatsAppLogController::class, 'index'])->name('whats-app-logs.index');
    Route::get('whats-app-logs/export/pdf', [WhatsAppLogController::class, 'exportPdf'])->name('whats-app-logs.export-pdf');
    Route::get('whats-app-logs/export/excel', [WhatsAppLogController::class, 'exportExcel'])->name('whats-app-logs.export-excel');

    Route::prefix('qrattendance')->name('qrattendance.')->group(function () {
        Route::get('/scanner', [QrAttendanceController::class, 'index'])->name('index');
        Route::post('/scan', [QrAttendanceController::class, 'scan'])->name('scan');
    });

    // =========================
    // ID Card Search Pages
    // =========================
    Route::get('student-id-cards', [WebsiteController::class, 'studentIdCardPage'])->name('student.id.cards');
    Route::get('teacher-id-cards', [WebsiteController::class, 'teacherIdCardPage'])->name('teacher.id.cards');
    Route::get('staff-id-cards', [WebsiteController::class, 'staffIdCardPage'])->name('staff.id.cards');

    // =========================
    // Print Pages
    // =========================
    Route::get('student-list-for-card-print/{encodedData}', [WebsiteController::class, 'studentIdCardList'])->name('student.id.card.print');
    Route::get('teacher-list-for-card-print', [WebsiteController::class, 'teacherIdCardList'])->name('teacher.id.card.print');
    Route::get('staff-list-for-card-print', [WebsiteController::class, 'staffIdCardList'])->name('staff.id.card.print');

    // Google Meet
    Route::resource('google-meet', GoogleMeetController::class)->except(['show']);
    Route::post('google-meet/{id}/cancel', [GoogleMeetController::class, 'cancel'])->name('google-meet.cancel');
    Route::post('google-meet/{id}/duplicate', [GoogleMeetController::class, 'duplicate'])->name('google-meet.duplicate');
    Route::get('google-meet/{id}/print', [GoogleMeetController::class, 'print'])->name('google-meet.print');
    Route::get('google-meet-export/pdf', [GoogleMeetController::class, 'exportPdf'])->name('google-meet.export-pdf');
    Route::get('google-meet-export/excel', [GoogleMeetController::class, 'exportExcel'])->name('google-meet.export-excel');

    Route::get('google-meet/oauth/connect', [GoogleMeetSettingController::class, 'connect'])->name('google-meet.oauth.connect');
    Route::get('google-meet/oauth/callback', [GoogleMeetSettingController::class, 'callback'])->name('google-meet.oauth.callback');
    Route::post('google-meet/test-connection', [GoogleMeetSettingController::class, 'testConnection'])->name('google-meet.test-connection');
});
