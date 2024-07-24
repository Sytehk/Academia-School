<?php

namespace Modules\Examination\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Academic\Services\ClassService;
use Modules\Academic\Services\StudentService;
use Modules\Examination\Models\ClassExam;
use Modules\Examination\Models\ExamCode;
use Modules\Examination\Models\ExamGrade;
use Modules\Examination\Models\Grade;
use Modules\Examination\Models\MeritProcessType;
use Modules\Examination\Models\ShortCode;

class SemesterExamSettingController extends Controller
{
    public function __construct(
        private readonly ClassService $classService,
        private readonly StudentService $studentService,
    ) {}

    public function grades(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        try {
            $grads = Grade::orderBy('id', 'DESC')->paginate($perPage);

            return $this->responseSuccess(
                $grads,
                _lang('Grade has been fetched successfully.')
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function shortCode(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        try {
            $shortCode = ShortCode::orderBy('id', 'DESC')->paginate($perPage);

            return $this->responseSuccess(
                $shortCode,
                _lang('Short Code has been fetched successfully.')
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function classExam(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        try {
            $classExam = ClassExam::orderBy('id', 'DESC')->with('exam', 'class', 'meritType')->paginate($perPage);

            return $this->responseSuccess(
                $classExam,
                _lang('Class Exam has been fetched successfully.')
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function examCode(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        try {
            $examCode = ExamCode::orderBy('id', 'DESC')->with('class', 'shortCode')->paginate($perPage);

            return $this->responseSuccess(
                $examCode,
                _lang('ExamCode has been fetched successfully.')
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function examGrade(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        try {
            $examGrade = ExamGrade::orderBy('id', 'DESC')->with('class', 'grade')->paginate($perPage);

            return $this->responseSuccess(
                $examGrade,
                _lang('ExamGrade has been fetched successfully.')
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function meritProcessType(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        try {
            $meritProcessType = MeritProcessType::orderBy('id', 'DESC')->paginate($perPage);

            return $this->responseSuccess(
                $meritProcessType,
                _lang('Merit Process Type has been fetched successfully.')
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function examGradeStore(Request $request): JsonResponse
    {
        // Validate the incoming request
        $request->validate([
            'class_id' => 'required|exists:classes,id', // Ensure class_id exists in the classes table
            'selected_grades' => 'required|array', // Ensure selected_grades is an array
            'selected_grades.*' => 'exists:grades,id', // Ensure each selected code exists in the grades table
        ]);

        // Retrieve the class model using the class_id from the request
        $classId = (int) $request->class_id;
        // Loop through selected codes and attach them to the class
        foreach ($request->selected_grades as $gradeId) {
            $grade = Grade::where('id', (int) $gradeId)->first();

            if ($grade) {
                // Create or update the ExamCode based on class_id and gradeId
                ExamGrade::updateOrCreate(
                    [
                        'institute_id' => get_institute_id(),
                        'branch_id' => get_branch_id(),
                        'session_id' => get_option('academic_year'),
                        'class_model_id' => $classId,
                        'grade_id' => $grade->id,
                    ],
                    [
                        'grade_name' => $grade->grade_name,
                        'grade_number' => $grade->grade_number,
                        'grade_point' => $grade->grade_point,
                        'grade_range' => $grade->grade_range,
                        'number_high' => $grade->number_high,
                        'number_low' => $grade->number_low,
                        'point_high' => $grade->point_high,
                        'point_low' => $grade->point_low,
                        'priority' => $grade->priority,
                    ]
                );
            }
        }

        return $this->responseSuccess(
            [],
            _lang('Exam Grade create successfully.')
        );
    }

    public function examGradeUpdate(Request $request): JsonResponse
    {
        // Validate the incoming request
        $request->validate([
            'checked_grades' => 'required|array', // Ensure selected grades are provided
            'checked_grades.*' => 'exists:grades,id', // Ensure each selected grade exists
            'grades' => 'required|array', // Ensure grades array is present
            'grades.*.name' => 'required|string', // Validate grade names
            'grades.*.range' => 'required|string', // Validate grade ranges
        ]);

        // Loop through selected grade IDs
        foreach ($request->checked_grades as $gradeId) {
            if (isset($request->grades[$gradeId])) {
                $gradeData = $request->grades[$gradeId];

                // Update the ExamGrade based on the grade ID
                ExamGrade::where('id', $gradeId)
                    ->where('institute_id', get_institute_id())
                    ->where('branch_id', get_branch_id())
                    ->update([
                        'grade_name' => $gradeData['name'],
                        'grade_range' => $gradeData['range'],
                        // Add other fields as necessary
                    ]);
            }
        }

        return $this->responseSuccess(
            [],
            _lang('Exam grades updated successfully.')
        );
    }

    public function examCodeStore(Request $request): JsonResponse
    {
        // Validate the incoming request
        $request->validate([
            'class_id' => 'required|exists:classes,id', // Ensure class_id exists in the classes table
            'selected_codes' => 'required|array', // Ensure selected_codes is an array
            'selected_codes.*' => 'exists:short_codes,id', // Ensure each selected code exists in the short_codes table
        ]);

        // Retrieve the class model using the class_id from the request
        $classId = (int) $request->class_id;
        // Loop through selected codes and attach them to the class
        foreach ($request->selected_codes as $shortCodeId) {
            $shortCode = ShortCode::where('id', (int) $shortCodeId)->first();

            if ($shortCode) {
                // Create or update the ExamCode based on class_id and shortCodeId
                ExamCode::updateOrCreate(
                    [
                        'institute_id' => get_institute_id(),
                        'branch_id' => get_branch_id(),
                        'session_id' => get_option('academic_year'),
                        'class_model_id' => $classId,
                        'short_code_id' => $shortCode->id,
                    ],
                    [
                        'short_code_title' => $shortCode->short_code_title,
                        'short_code_note' => $shortCode->short_code_note,
                        'default_id' => $shortCode->default_id,
                        'total_mark' => $shortCode->total_mark,
                        'accept_percent' => $shortCode->accept_percent,
                        'pass_mark' => $shortCode->pass_mark,
                    ]
                );
            }
        }

        return $this->responseSuccess(
            [],
            'Exam codes create successfully.'
        );
    }

    public function examCodeUpdate(Request $request): JsonResponse
    {
        // Validate the incoming request
        $request->validate([
            'class_ids' => 'required|array',
            'class_ids.*' => 'exists:classes,id',
            'short_code_ids' => 'required|array',
            'short_code_ids.*' => 'exists:short_codes,id',
            'short_codes_title' => 'required|array',
            'short_codes_title.*' => 'string',
        ]);

        // Loop through selected short_code_ids
        foreach ($request->short_code_ids as $shortCodeId) {
            $shortCode = ShortCode::find($shortCodeId);

            // Ensure the corresponding title is available
            if ($shortCode && isset($request->short_codes_title[$shortCodeId])) {
                $title = $request->short_codes_title[$shortCodeId];

                // Loop through selected class_ids
                foreach ($request->class_ids as $classId) {
                    // Update the ExamCode title based on class_id and short_code_id
                    ExamCode::where('class_model_id', $classId)
                        ->where('short_code_id', $shortCodeId)
                        ->update([
                            'short_code_title' => $title, // Update only the title
                        ]);
                }
            }
        }

        return $this->responseSuccess(
            [],
            'Exam codes updated successfully.'
        );
    }
}
