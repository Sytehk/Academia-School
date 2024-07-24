<?php

namespace Modules\Examination\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Academic\Models\Subject;
use Modules\Academic\Services\StudentService;
use Modules\Examination\Http\Requests\ExamAssignRequest;
use Modules\Examination\Models\ClassExam;
use Modules\Examination\Models\ExamCode;
use Modules\Examination\Models\GrandFinalClassExam;
use Modules\Examination\Models\GrandFinalSetting;
use Modules\Examination\Models\MarkConfig;
use Modules\Examination\Models\MarkConfigExamCode;
use Modules\Examination\Services\MarkEntryService;
use Modules\Examination\Services\TabulationSheetService;

class ExamMarkInputController extends Controller
{
    public function __construct(
        private readonly StudentService $studentService,
        private readonly MarkEntryService $markEntryService,
        private readonly AcademicContextService $academicContext,
    ) {}

    public function markConfig(Request $request): JsonResponse
    {
        $classId = (int) $request->class_id;
        $groupId = (int) $request->group_id;

        if ($classId) {
            $this->academicContext->assertClassSectionGroup($classId, null, $groupId ?: null);
        }
        $data = [];

        // Initialize variables for subjects and class exams
        $subjects = collect();
        $classExams = collect();
        $examCodes = collect();
        $markConfig = collect();

        // If classId is provided, fetch subjects and class exams
        if ($classId) {
            $subjects = Subject::where('class_id', $classId)
                ->where('group_id', $groupId)
                ->select('id', 'class_id', 'subject_name', 'subject_code')
                ->get();

            $classExams = ClassExam::where('class_id', $classId)->with('exam', 'class', 'meritType')->get();
            $examCodes = ExamCode::where('class_model_id', $classId)->with('class', 'shortCode')->get();
            // Fetch MarkConfig based on class_id and group_id
            if ($request->type == 'mark_config_view') {
                $examId = (int) $request->exam_id;
                $markConfig = MarkConfig::with(['class', 'group', 'subject', 'exam', 'mark_config_exam_codes'])
                    ->where('class_id', $classId)
                    ->where('group_id', $groupId)
                    ->where('exam_id', $examId)
                    ->get();
            }
        }

        $data['classId'] = $classId;
        $data['groupId'] = $groupId;
        $data['subjects'] = $subjects;
        $data['classExams'] = $classExams;
        $data['examCodes'] = $examCodes;
        $data['markConfig'] = $markConfig;

        return $this->responseSuccess(
            $data,
            'Exam codes updated successfully.'
        );
    }

    public function grandFinalMarkPercentage($class_id)
    {
        try {
            // fetch existing grand final data
            $grandFinalExams = GrandFinalClassExam::with('exam:id,name')
                ->where('class_id', $class_id)
                ->get();

            if ($grandFinalExams->isNotEmpty()) {
                $data = $grandFinalExams->map(function ($item) {
                    return [
                        'exam_id' => $item->exam_id,
                        'exam_name' => $item->exam->name ?? 'Unknown',
                        'percentage' => (float) $item->percentage,
                        'serial_no' => (int) $item->serial_no,
                    ];
                });
            } else {
                // fallback to class_exams if not yet configured
                $classExams = ClassExam::with('exam:id,name')
                    ->where('class_id', $class_id)
                    ->get();

                $data = $classExams->map(function ($item) {
                    return [
                        'exam_id' => $item->exam_id,
                        'exam_name' => $item->exam->name ?? 'Unknown',
                        'percentage' => 0,
                        'serial_no' => 1,
                    ];
                });
            }

            $calculationMethod = GrandFinalSetting::where('class_id', $class_id)->value('calculation_method')
                ?? GrandFinalSetting::METHOD_WEIGHTED_PERCENTAGE;

            return $this->responseSuccess(
                [
                    'status' => true,
                    'class_id' => $class_id,
                    'calculation_method' => $calculationMethod,
                    'data' => $data,
                ],
                _lang('Grand Final Mark class wise percentage')
            );
        } catch (Exception $e) {
            return $this->responseError([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function generalExamStore(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'group_id' => 'required|exists:student_groups,id',

            'selected_subjects' => 'required|array|min:1',
            'selected_subjects.*' => 'exists:subjects,id',

            'selected_exams' => 'required|array|min:1',
            'selected_exams.*' => 'exists:exams,id',

            'exam_codes' => 'required|array|min:1',
            'exam_codes.*.title' => 'required|string|max:50',
            'exam_codes.*.total_marks' => 'required|numeric|min:0',
            'exam_codes.*.pass_mark' => 'required|numeric|min:0',
            'exam_codes.*.acceptance' => 'nullable|numeric|min:0|max:100',
        ]);

        $this->academicContext->assertClassSectionGroup(
            (int) $request->class_id,
            null,
            (int) $request->group_id,
        );

        DB::beginTransaction();

        try {

            $classId = (int) $request->class_id;
            $groupId = (int) $request->group_id;

            $subjects = array_map('intval', $request->selected_subjects);
            $exams = array_map('intval', $request->selected_exams);
            $codes = $request->exam_codes;

            // Hoisted out of the loops. These three are constant for the whole
            // request, but were being re-read on every one of the innermost
            // iterations -- twice each, once per updateOrCreate. With 12 subjects
            // x 4 exams x 4 codes that was 192 iterations x 6 settings reads, and
            // get_option() cost two queries a call before it was memoised.
            $instituteId = get_institute_id();
            $branchId = get_branch_id();
            $sessionId = get_option('academic_year');

            foreach ($subjects as $subjectId) {
                foreach ($exams as $examId) {
                    foreach ($codes as $code) {

                        $examCode = MarkConfigExamCode::updateOrCreate(
                            [
                                'institute_id' => $instituteId,
                                'branch_id' => $branchId,
                                'session_id' => $sessionId,
                                'title' => $code['title'],
                                'subject_id' => $subjectId,
                            ],
                            [
                                'total_marks' => $code['total_marks'],
                                'pass_mark' => $code['pass_mark'],
                                'acceptance' => $code['acceptance'] ?? 0,
                            ]
                        );

                        MarkConfig::updateOrCreate(
                            [
                                'institute_id' => $instituteId,
                                'branch_id' => $branchId,
                                'session_id' => $sessionId,
                                'class_id' => $classId,
                                'group_id' => $groupId,
                                'subject_id' => $subjectId,
                                'exam_id' => $examId,
                                'mark_config_exam_code_id' => $examCode->id,
                            ],
                            [
                                'updated_at' => now(),
                            ]
                        );
                    }
                }
            }

            DB::commit();

            return $this->responseSuccess([], _lang('Mark Config saved successfully.'));
        } catch (Exception $e) {

            DB::rollBack();

            return $this->responseError([], $e->getMessage(), 500);
        }
    }

    public function grandFinalExamStore(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'percentages' => 'required|array',
            'serial_no' => 'required|array',
            'calculation_method' => 'nullable|in:weighted_percentage,full_marks',
        ]);

        $calculationMethod = $request->calculation_method ?? GrandFinalSetting::METHOD_WEIGHTED_PERCENTAGE;

        if ($calculationMethod === GrandFinalSetting::METHOD_WEIGHTED_PERCENTAGE) {
            $percentageSum = collect($request->percentages)->sum('percentage');

            if (abs($percentageSum - 100) > 0.01) {
                return $this->responseError(
                    [],
                    _lang('Percentages must total 100% for the Weighted Percentage method.'),
                    422
                );
            }
        }

        GrandFinalSetting::updateOrCreate(
            [
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'session_id' => get_option('academic_year'),
                'class_id' => intval($request->class_id),
            ],
            [
                'calculation_method' => $calculationMethod,
            ]
        );

        foreach ($request->percentages as $exam) {
            $examId = $exam['exam_id'];
            $percentage = $exam['percentage'];
            $serialNo = collect($request->serial_no)->where('exam_id', $examId)->first()['serial_no'];

            GrandFinalClassExam::updateOrCreate(
                [
                    'institute_id' => get_institute_id(),
                    'branch_id' => get_branch_id(),
                    'session_id' => get_option('academic_year'),
                    'class_id' => intval($request->class_id),
                    'exam_id' => intval($examId),
                ],
                [
                    'percentage' => floatval($percentage),
                    'serial_no' => intval($serialNo),
                ]
            );
        }

        return $this->responseSuccess(
            [],
            _lang('Grand Final Percentage Created successfully.')
        );
    }

    public function markInputSectionWiseClass(Request $request, int $classId): JsonResponse
    {
        $examId = $request->filled('exam_id') ? (int) $request->exam_id : null;
        $groupId = $request->filled('group_id') ? (int) $request->group_id : null;
        $subjectId = $request->filled('subject_id') ? (int) $request->subject_id : null;
        $sectionId = $request->filled('section_id') ? (int) $request->section_id : null;
        $markConfig = collect();
        $students = collect();

        $data = [];
        if ($examId && $subjectId) {
            $markConfigQuery = MarkConfig::with(['class', 'group', 'subject', 'exam', 'mark_config_exam_code'])
                ->where('class_id', $classId)
                ->where('exam_id', $examId)
                ->where('subject_id', $subjectId);

            if ($groupId) {
                $markConfigQuery->where('group_id', $groupId);
            }

            $markConfig = $markConfigQuery->get();

            if ($sectionId) {
                $students = $this->studentService->getStudentsByClassSectionGroup(
                    $classId,
                    $sectionId,
                    $groupId ?: 0,
                    null,
                    get_institute_id(),
                    get_branch_id()
                );
            }
        }

        $data['markConfig'] = $markConfig;
        $data['students'] = $students;

        return $this->responseSuccess(
            $data,
            'Mark Input Section wise students get successfully.'
        );
    }

    public function markStoreSectionWise(Request $request): JsonResponse
    {
        // Validate input
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'group_id' => 'nullable|exists:student_groups,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_id' => 'required|exists:exams,id',
            'marks' => 'required|array|min:1',
            'marks.*.student_id' => 'required|exists:students,id',
            'marks.*.mark_1' => 'nullable|numeric|min:0',
            'marks.*.mark_2' => 'nullable|numeric|min:0',
            'marks.*.mark_3' => 'nullable|numeric|min:0',
            'marks.*.mark_4' => 'nullable|numeric|min:0',
            'marks.*.mark_5' => 'nullable|numeric|min:0',
            'marks.*.mark_6' => 'nullable|numeric|min:0',
        ]);

        $classId = (int) $validated['class_id'];
        $groupId = isset($validated['group_id']) ? (int) $validated['group_id'] : null;
        $subjectId = (int) $validated['subject_id'];
        $examId = (int) $validated['exam_id'];
        $marks = $validated['marks'];

        $this->academicContext->assertClassSectionGroup($classId, null, $groupId);
        $this->academicContext->assertStudentsInClassSection(
            array_column($marks, 'student_id'),
            $classId,
            null,
            $groupId,
        );

        $instituteId = get_institute_id();
        $branchId = get_branch_id();
        $sessionId = get_option('academic_year');

        $responseData = [];
        try {
            DB::beginTransaction();

            foreach ($marks as $studentMarks) {
                $studentId = (int) $studentMarks['student_id'];

                $markValuesByQuestion = [];
                foreach ($studentMarks as $key => $mark) {
                    if ($key === 'student_id') {
                        continue;
                    }

                    // Extract question number from keys like mark_1 => 1
                    $questionNumber = (int) filter_var($key, FILTER_SANITIZE_NUMBER_INT);
                    $markValuesByQuestion[$questionNumber] = $mark;
                }

                $identifiers = [
                    'institute_id' => $instituteId,
                    'branch_id' => $branchId,
                    'session_id' => $sessionId,
                    'student_id' => $studentId,
                    'class_id' => $classId,
                    'group_id' => $groupId,
                    'subject_id' => $subjectId,
                    'exam_id' => $examId,
                ];

                $examMark = $this->markEntryService->saveSectionWiseMarks($identifiers, $markValuesByQuestion);

                $responseData[] = [
                    'student_id' => $studentId,
                    'total_marks' => $examMark->total_marks,
                    'grade' => $examMark->grade,
                    'grade_point' => $examMark->grade_point,
                    'status' => 'stored',
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Marks stored section-wise successfully.',
                'data' => $responseData,
            ]);
        } catch (ValidationException $e) {
            // e.g. the result is locked — a 422 the client can show, not a 500.
            DB::rollBack();

            throw $e;
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to store marks.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function examResult(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'group_id' => 'nullable|exists:student_groups,id',
            'exam_id' => 'required|exists:exams,id',
            'search' => 'nullable|string',
        ]);

        try {
            $sheet = app(TabulationSheetService::class)->build(
                (int) $request->class_id,
                (int) $request->exam_id,
                $request->filled('section_id') ? (int) $request->section_id : null,
                $request->filled('group_id') ? (int) $request->group_id : null,
            );

            $rows = collect($sheet['rows']);
            if ($request->filled('search')) {
                $search = strtolower((string) $request->search);
                $rows = $rows->filter(function (array $row) use ($search) {
                    return str_contains(strtolower((string) $row['roll']), $search)
                        || str_contains(strtolower((string) $row['student_name']), $search);
                })->values();
            }

            $passCount = $rows->where('status', 'pass')->count();
            $failCount = $rows->where('status', 'fail')->count();

            $data = $rows->map(function (array $row) {
                $subjects = [];
                foreach ($row['subject_cells'] as $subjectId => $cell) {
                    $subjects[] = [
                        'subject_id' => $subjectId,
                        'total_marks' => $cell['obtained'],
                        'grade' => $cell['grade'],
                        'grade_point' => $cell['grade_point'],
                        'status' => $cell['status'],
                    ];
                }

                return [
                    'student_id' => $row['student_id'],
                    'student_name' => $row['student_name'],
                    'roll' => $row['roll'],
                    'total_marks' => $row['total_marks'],
                    'percentage' => $row['percentage'],
                    'gpa' => $row['gpa'],
                    'grade' => $row['grade'],
                    'status' => $row['status'],
                    'position' => $row['position'],
                    'failed_subject_count' => $row['failed_subject_count'],
                    'subjects' => $subjects,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Exam results fetched successfully.',
                'summary' => [
                    'total_students' => $rows->count(),
                    'passed' => $passCount,
                    'failed' => $failCount,
                ],
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch results.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function examAssignStore(ExamAssignRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $classId = (int) $request->class_id;
            $meritProcessTypeId = (int) $request->merit_process_type_id;

            // Check for existing assignments in a single query
            $existingExams = ClassExam::where('class_id', $classId)
                ->where('merit_process_type_id', $meritProcessTypeId)
                ->whereIn('exam_id', $request->exam_ids)
                ->pluck('exam_id')
                ->toArray();

            // Filter out existing exams
            $newExams = array_diff($request->exam_ids, $existingExams);
            if (empty($newExams)) {
                return $this->responseError([], _lang('All selected exams for this class and merit process type already exist.'), 422);
            }

            // Prepare bulk insert data
            $insertData = [];
            foreach ($newExams as $examId) {
                $insertData[] = [
                    'institute_id' => get_institute_id(),
                    'branch_id' => get_branch_id(),
                    'session_id' => get_option('academic_year'),
                    'class_id' => $classId,
                    'exam_id' => $examId,
                    'merit_process_type_id' => $meritProcessTypeId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Insert all new records in one query
            ClassExam::insert($insertData);

            DB::commit();

            return $this->responseSuccess(
                [],
                _lang('Exam merit assignments created successfully.')
            );
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError([], $e->getMessage(), 500);
        }
    }
}
