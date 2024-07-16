<?php

namespace Modules\Frontend\Http\Controllers\API;

use App\Helpers\InstituteHelper;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Student;
use Modules\Authentication\Models\Branch;
use Modules\Examination\Models\ClassExam;
use Modules\Examination\Models\ExamMark;
use Modules\Examination\Models\Grade;
use Modules\Frontend\Http\Requests\AdmissionForm\AdmissionFormStoreRequest;
use Modules\Frontend\Http\Requests\AdmissionForm\AdmissionFormUpdateRequest;
use Modules\Frontend\Models\AdmissionForm;
use Modules\Frontend\Repositories\AdmissionFormRepository;

class AdmissionFormController extends Controller
{
    public function __construct(private AdmissionFormRepository $repo) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getAll(request()->all()), 'AdmissionForm has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(AdmissionFormStoreRequest $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $branchId = Branch::where('institute_id', $instituteId)->first()?->id;
            // ✅ validated data
            $data = $request->validated();

            // ✅ institute (if multi tenant later)
            $data['institute_id'] = $instituteId;
            $data['branch_id'] = $branchId;

            // ✅ IMAGE UPLOAD
            if ($request->hasFile('image')) {
                $data['image'] = fileUploader('users/', 'png', $request->file('image'));
            }

            // ✅ STORE IN DATABASE
            $admissionForm = AdmissionForm::create($data);

            return $this->responseSuccess($admissionForm, 'AdmissionForm has been created successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function show($id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getById($id), 'AdmissionForm has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function update(AdmissionFormUpdateRequest $request, $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->update($id, $request->all()), 'AdmissionForm has been updated successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->delete($id), 'AdmissionForm has been deleted successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function examResult(Request $request)
    {
        $instituteId = $this->getInstituteIdFromHeader($request);

        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        // Class List
        $classes = ClassModel::where('institute_id', $instituteId)
            ->select('id', 'class_name')
            ->get();

        // Exam List (Dynamic when class selected)
        $exams = collect();

        if ($request->filled('class_id')) {

            $exams = ClassExam::with('exam:id,name')
                ->where('institute_id', $instituteId)
                ->where('class_id', $request->class_id)
                ->get()
                ->pluck('exam');
        }

        // If no search yet
        if (
            ! $request->filled('class_id') ||
            ! $request->filled('exam_id')
        ) {

            return $this->responseSuccess([
                'classes' => $classes,
                'exams' => $exams,
                'student' => null,
                'results' => [],
                'summary' => [],
            ], 'Result page data.');
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'exam_id' => 'required|exists:exams,id',
            'roll_no' => 'nullable',
            'qr_code' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->responseError(
                $validator->errors(),
                $validator->errors()->first(),
                422
            );
        }

        if (
            ! $request->filled('roll_no') &&
            ! $request->filled('qr_code')
        ) {

            return $this->responseError(
                [],
                'Either Roll No or QR Code is required.',
                422
            );
        }

        // Verify class exam
        $classExam = ClassExam::where([
            'institute_id' => $instituteId,
            'class_id' => $request->class_id,
            'exam_id' => $request->exam_id,
        ])->first();

        if (! $classExam) {

            return $this->responseError(
                [],
                'Selected exam is not assigned to this class.',
                422
            );
        }

        // Student Query
        $studentQuery = Student::with([
            'studentSession.class',
            'studentSession.section',
            'studentSession.group',
            'user',
        ])->where('institute_id', $instituteId);

        if ($request->filled('qr_code')) {

            $studentQuery->whereHas('studentSession', function ($q) use ($request) {

                $q->where('class_id', $request->class_id)
                    ->where('qr_code', $request->qr_code);
            });
        } else {

            $studentQuery->whereHas('studentSession', function ($q) use ($request) {

                $q->where('class_id', $request->class_id)
                    ->where('roll', $request->roll_no);
            });
        }

        $student = $studentQuery->first();

        if (! $student) {

            return $this->responseError(
                [],
                'Student not found.',
                404
            );
        }

        // Student Result
        $results = ExamMark::with([
            'subject',
            'exam',
        ])
            ->where('student_id', $student->id)
            ->where('exam_id', $request->exam_id)
            ->orderBy('subject_id')
            ->get();

        if ($results->isEmpty()) {

            return $this->responseError(
                [],
                'No result found for this exam.',
                404
            );
        }

        // Summary
        $totalMarks = $results->sum('total_marks');

        $subjectCount = $results->count();

        $average = $subjectCount
            ? round($totalMarks / $subjectCount, 2)
            : 0;

        $grade = Grade::where('number_low', '<=', $average)
            ->where('number_high', '>=', $average)
            ->first();

        $failed = $results->contains(function ($mark) {
            return $mark->total_marks < 33;
        });

        $summary = [
            'total_marks' => $totalMarks,
            'average' => $average,
            'grade' => $grade->grade_name ?? 'F',
            'gpa' => $grade->grade_point ?? 0,
            'status' => $failed ? 'FAILED' : 'PASSED',
            'subject_count' => $subjectCount,
        ];

        return $this->responseSuccess([
            'classes' => $classes,
            'exams' => $exams,
            'student' => $student,
            'results' => $results,
            'summary' => $summary,
        ], 'Result fetched successfully.');
    }

    private function getInstituteIdFromHeader(Request $request): mixed
    {
        $domain = $request->header('X-Domain'); // Custom header key (Change if needed)
        if (! $domain) {
            return $this->responseError([], 'Domain header is missing.', 400);
        }

        $instituteResponse = InstituteHelper::getInstituteIdByDomain($domain);
        if (! $instituteResponse['status']) {
            return $this->responseError([], $instituteResponse['error'], 404);
        }

        return $instituteResponse['institute_id'];
    }
}
