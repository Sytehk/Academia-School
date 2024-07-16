<?php

namespace Modules\Frontend\Http\Controllers\WEB;

use App\Helpers\InstituteHelper;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Student;
use Modules\Examination\Models\ExamMark;
use Modules\Examination\Services\GradeCalculationService;
use Modules\Frontend\Http\Requests\AdmissionForm\AdmissionFormStoreRequest;
use Modules\Frontend\Models\AcademicImage;
use Modules\Frontend\Models\AdmissionForm;

class AdmissionFormFrontendController extends Controller
{
    public function __construct(
        private readonly GradeCalculationService $gradeCalculationService,
    ) {}

    public function index(Request $request)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        $classes = ClassModel::query()
            ->where('institute_id', optional($institute)->id)
            ->get();

        return view('themes.theme-one.pages.admission_form', [
            'classes' => $classes,
        ]);
    }

    public function store(AdmissionFormStoreRequest $request)
    {
        try {
            // ✅ validated data
            $data = $request->validated();

            $institute = InstituteHelper::resolveInstituteFromRequest($request);
            $branchId = $institute ? InstituteHelper::resolveBranchIdFromRequest($request) : null;

            $data['institute_id'] = optional($institute)->id;
            $data['branch_id'] = $branchId;

            // ✅ IMAGE UPLOAD
            if ($request->hasFile('image')) {
                $data['image'] = fileUploader('users/', 'png', $request->file('image'));
            }

            // ✅ STORE IN DATABASE
            AdmissionForm::create($data);

            // ✅ SUCCESS MESSAGE
            return redirect()
                ->route('admission-form-page')
                ->with('success', 'Admission submitted successfully!');
        } catch (Exception $e) {

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function resultPage(Request $request)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        $classes = ClassModel::query()
            ->when($institute, function ($query) use ($institute): void {
                $query->where('institute_id', $institute->id);
            })
            ->get();

        $student = null;
        $results = collect();
        $summary = [];

        // =========================
        // GLOBAL SEARCH TRIGGER
        // =========================
        if (
            $request->filled('class_id') ||
            $request->filled('roll_no') ||
            $request->filled('qr_code')
        ) {

            $studentQuery = Student::with([
                'studentSession.class',
                'studentSession.section',
                'studentSession.group',
            ])->when($institute, function ($query) use ($institute): void {
                $query->where('institute_id', $institute->id);
            });

            // =========================
            // QR CODE (HIGHEST PRIORITY)
            // =========================
            if ($request->filled('qr_code')) {

                $student = $studentQuery->whereHas('studentSession', function ($q) use ($request) {
                    $q->where('qr_code', $request->qr_code);
                })->first();
            } else {

                // =========================
                // NORMAL FILTER SEARCH
                // =========================
                $student = $studentQuery

                    ->when($request->class_id, function ($q) use ($request) {
                        $q->whereHas('studentSession', function ($sq) use ($request) {
                            $sq->where('class_id', $request->class_id);
                        });
                    })

                    ->when($request->roll_no, function ($q) use ($request) {
                        $q->whereHas('studentSession', function ($sq) use ($request) {
                            $sq->where('roll', $request->roll_no);
                        });
                    })

                    ->first();
            }

            // =========================
            // RESULT SECTION
            // =========================
            if ($student) {

                $results = ExamMark::with(['subject', 'exam'])
                    ->where('student_id', $student->id)
                    ->when($institute, function ($query) use ($institute): void {
                        $query->where('institute_id', $institute->id);
                    })
                    ->orderBy('subject_id')
                    ->get();

                $totalMarks = $results->sum('total_marks');
                $subjectCount = $results->count();
                $average = $subjectCount > 0 ? round($totalMarks / $subjectCount, 2) : 0;

                $grade = $this->gradeCalculationService->resolveGrade(
                    (int) ($student->studentSession->class_id ?? 0),
                    $average,
                    optional($institute)->id,
                );

                $gpa = $grade->grade_point ?? 0;
                $finalGrade = $grade->grade_name ?? 'F';

                $failed = $results->contains(function ($mark) {
                    return $mark->total_marks < 33;
                });

                $summary = [
                    'total_marks' => $totalMarks,
                    'average' => $average,
                    'grade' => $finalGrade,
                    'gpa' => $gpa,
                    'status' => $failed ? 'FAILED' : 'PASSED',
                    'subject_count' => $subjectCount,
                ];
            }
        }

        return view('themes.theme-one.pages.result_page', compact(
            'institute',
            'classes',
            'student',
            'results',
            'summary'
        ));
    }

    public function printResult($id)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest(request());
        $student = Student::query()
            ->with(['studentSession.class', 'studentSession.section', 'studentSession.group'])
            ->where('id', $id)
            ->where('institute_id', optional($institute)->id)
            ->firstOrFail();

        $results = ExamMark::with(['subject', 'exam'])
            ->where('student_id', $student->id)
            ->when($institute, function ($query) use ($institute): void {
                $query->where('institute_id', $institute->id);
            })
            ->get();

        $pdf = Pdf::loadView(
            'themes.theme-one.pages.result_pdf',
            compact('institute', 'student', 'results')
        )->setPaper('a4');

        return $pdf->download('result.pdf');
    }

    public function academicImages(Request $request)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        $academicImages = AcademicImage::query()
            ->when($institute, function ($query) use ($institute): void {
                $query->where('institute_id', $institute->id);
            })
            ->get();

        return view('themes.theme-one.pages.academic_images', [
            'academicImages' => $academicImages,
        ]);
    }
}
