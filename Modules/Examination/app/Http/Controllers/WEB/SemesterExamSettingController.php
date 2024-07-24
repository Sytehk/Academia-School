<?php

namespace Modules\Examination\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Subject;
use Modules\Academic\Services\ClassService;
use Modules\Academic\Services\StudentService;
use Modules\Examination\Models\ClassExam;
use Modules\Examination\Models\ExamCode;
use Modules\Examination\Models\ExamGrade;
use Modules\Examination\Models\Grade;
use Modules\Examination\Models\GrandFinalClassExam;
use Modules\Examination\Models\GrandFinalSetting;
use Modules\Examination\Models\MarkConfig;
use Modules\Examination\Models\MarkConfigExamCode;
use Modules\Examination\Models\ShortCode;
use Modules\Examination\Services\MarkEntryService;
use RealRashid\SweetAlert\Facades\Alert;

class SemesterExamSettingController extends Controller
{
    public function __construct(
        private readonly ClassService $classService,
        private readonly StudentService $studentService,
        private readonly MarkEntryService $markEntryService,
    ) {}

    public function examStartUp(): Factory|View
    {
        $grades = Grade::get();
        $shortCodes = ShortCode::get();
        $classExams = ClassExam::with('exam', 'class')->get();
        $examShortCodes = ExamCode::get();
        $examGrades = ExamGrade::get();

        return view('examination::exam_startup.index', compact('grades', 'shortCodes', 'classExams', 'examShortCodes', 'examGrades'));
    }

    public function examCodeStore(Request $request): Redirector|RedirectResponse
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
                // Only seed short_code_title/total_mark/accept_percent/pass_mark from the
                // ShortCode master on first creation. Re-checking this class/code pair on a
                // later visit must not revert any per-class edit made via examCodeUpdate() —
                // that was the cause of BUG-005 (edits reverting on refresh).
                $examCode = ExamCode::firstOrNew([
                    'institute_id' => get_institute_id(),
                    'branch_id' => get_branch_id(),
                    'session_id' => get_option('academic_year'),
                    'class_model_id' => $classId,
                    'short_code_id' => $shortCode->id,
                ]);

                if (! $examCode->exists) {
                    $examCode->short_code_title = $shortCode->short_code_title;
                    $examCode->short_code_note = $shortCode->short_code_note;
                    $examCode->default_id = $shortCode->default_id;
                    $examCode->total_mark = $shortCode->total_mark;
                    $examCode->accept_percent = $shortCode->accept_percent;
                    $examCode->pass_mark = $shortCode->pass_mark;
                    $examCode->save();
                }
            }
        }

        Alert::toast(_lang('Exam codes create successfully.'), 'success');

        return redirect('semester-exam-settings-exam-startup');
    }

    public function examCodeUpdate(Request $request): Redirector|RedirectResponse
    {
        // Validate the incoming request. exam_code_ids identifies rows by their own
        // exam_codes.id — updating by (class_model_id, short_code_id) previously compared
        // an exam_codes.id value against the short_code_id foreign key column, which only
        // matched by coincidence and silently updated zero rows otherwise (BUG-005).
        $request->validate([
            'exam_code_ids' => 'required|array',
            'exam_code_ids.*' => 'exists:exam_codes,id',
            'short_codes_title' => 'required|array',
            'short_codes_title.*' => 'string|max:191',
            'total_marks' => 'required|array',
            'total_marks.*' => 'numeric|min:0',
            'pass_marks' => 'required|array',
            'pass_marks.*' => 'numeric|min:0',
            'acceptance' => 'required|array',
            'acceptance.*' => 'numeric|min:0',
        ]);

        foreach ($request->exam_code_ids as $examCodeId) {
            $examCode = ExamCode::where('id', $examCodeId)
                ->where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->first();

            if (! $examCode) {
                continue;
            }

            $examCode->update([
                'short_code_title' => $request->short_codes_title[$examCodeId] ?? $examCode->short_code_title,
                'total_mark' => $request->total_marks[$examCodeId] ?? $examCode->total_mark,
                'pass_mark' => $request->pass_marks[$examCodeId] ?? $examCode->pass_mark,
                'accept_percent' => $request->acceptance[$examCodeId] ?? $examCode->accept_percent,
            ]);
        }

        Alert::toast(_lang('Exam codes updated successfully.'), 'success');

        return redirect('semester-exam-settings-exam-startup');
    }

    public function markConfig(Request $request): Factory|View
    {
        $classId = (int) $request->class_id;
        $groupId = (int) $request->group_id;

        // Initialize variables for subjects and class exams
        $subjects = collect();
        $classExams = collect();
        $examCodes = collect();
        $markConfig = collect();

        // If classId is provided, fetch subjects and class exams
        if ($classId) {
            $subjects = Subject::where('class_id', $classId)
                ->where(function ($query) use ($groupId) {
                    $query->where('group_id', $groupId)->orWhereNull('group_id');
                })
                ->get();
            $classExams = ClassExam::where('class_id', $classId)->get();
            $examCodes = ExamCode::where('class_model_id', $classId)->get();
            // Fetch MarkConfig based on class_id and group_id
            if ($request->type == 'mark_config_view') {
                $examId = (int) $request->exam_id;
                $markConfig = MarkConfig::with(['class', 'group', 'subject', 'exam', 'mark_config_exam_code'])
                    ->where('class_id', $classId)
                    ->where('group_id', $groupId)
                    ->where('exam_id', $examId)
                    ->get();
            }
        }

        // Fetch all classes for the dropdown or list
        $classes = $this->classService->getClasses();

        return view('examination::mark_config.index', compact('classes', 'classId', 'groupId', 'subjects', 'classExams', 'examCodes', 'markConfig'));
    }

    public function generalExamStore(Request $request): Redirector|RedirectResponse
    {
        // Validate the incoming data
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'group_id' => 'required|exists:student_groups,id',
            'selected_subjects' => 'required|array',
            'exam_codes' => 'required|array',
            'selected_exams' => 'required|array',
        ]);

        $classId = $request->input('class_id');
        $groupId = $request->input('group_id');
        $selectedSubjects = $request->input('selected_subjects');
        $examCodes = $request->input('exam_codes');
        $selectedExams = $request->input('selected_exams');

        DB::beginTransaction();
        try {
            foreach ($selectedSubjects as $subjectId) {
                foreach ($selectedExams as $examId) {
                    foreach ($examCodes as $code) {
                        // Update or create a new MarkConfigExamCode with unique title and subject_id combination
                        $examCode = MarkConfigExamCode::updateOrCreate(
                            [
                                'institute_id' => get_institute_id(),
                                'branch_id' => get_branch_id(),
                                'session_id' => get_option('academic_year'),
                                'title' => $code['title'],
                                'subject_id' => $subjectId, // Add subject_id to ensure uniqueness per subject
                            ],
                            [
                                'total_marks' => $code['total_marks'],
                                'pass_mark' => $code['pass_mark'],
                                'acceptance' => $code['acceptance'],
                            ]
                        );

                        if ($examCode && $examCode->id) {
                            // Proceed to create or update the MarkConfig only if examCode has a valid ID
                            MarkConfig::updateOrCreate(
                                [
                                    'institute_id' => get_institute_id(),
                                    'branch_id' => get_branch_id(),
                                    'session_id' => get_option('academic_year'),
                                    'class_id' => $classId,
                                    'group_id' => $groupId,
                                    'subject_id' => $subjectId,
                                    'exam_id' => $examId,
                                    'mark_config_exam_code_id' => $examCode->id,
                                ],
                                [
                                    'updated_at' => now(),
                                    'created_at' => now(),
                                ]
                            );
                        } else {
                            // Log error if exam code creation failed
                            Log::error('Failed to create or find MarkConfigExamCode for title: '.$code['title']);
                        }
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('Error', $e->getMessage());

            return redirect('semester-exam-settings-mark-config');
        }

        Alert::toast(_lang('Mark Config updated successfully.'), 'success');

        // Preserve class_id/group_id so the page reloads with the same selection instead of
        // its empty state — without this the save appeared to do nothing (BUG-006 / UI-003).
        return redirect()->to('semester-exam-settings-mark-config?class_id='.$classId.'&group_id='.$groupId);
    }

    public function grandFinalExamStore(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'percentages' => 'required|array',
            'serial_no' => 'required|array',
            'calculation_method' => 'nullable|in:weighted_percentage,full_marks',
        ]);

        $calculationMethod = $request->calculation_method ?? GrandFinalSetting::METHOD_WEIGHTED_PERCENTAGE;

        if ($calculationMethod === GrandFinalSetting::METHOD_WEIGHTED_PERCENTAGE) {
            $percentageSum = array_sum($request->percentages);

            if (abs($percentageSum - 100) > 0.01) {
                Alert::error('Error', _lang('Percentages must total 100% for the Weighted Percentage method.'));

                return redirect('semester-exam-settings-mark-config');
            }
        }

        GrandFinalSetting::updateOrCreate(
            [
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'session_id' => get_option('academic_year'),
                'class_id' => $request->class_id,
            ],
            [
                'calculation_method' => $calculationMethod,
            ]
        );

        foreach ($request->percentages as $examId => $percentage) {
            $serialNo = $request->serial_no[$examId];
            GrandFinalClassExam::updateOrCreate(
                [
                    'institute_id' => get_institute_id(),
                    'branch_id' => get_branch_id(),
                    'session_id' => get_option('academic_year'),
                    'class_id' => $request->class_id,
                    'exam_id' => $examId,
                ],
                [
                    'percentage' => $percentage,
                    'serial_no' => $serialNo,
                ]
            );
        }

        Alert::toast(_lang('Grand Final Percentage Create successfully.'), 'success');

        return redirect('semester-exam-settings-mark-config');
    }

    public function examGradeStore(Request $request): Redirector|RedirectResponse
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

        Alert::toast(_lang('Exam Grade create successfully.'), 'success');

        return redirect('semester-exam-settings-exam-startup');
    }

    public function examGradeUpdate(Request $request): Redirector|RedirectResponse
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
                        'institute_id' => get_institute_id(),
                        'branch_id' => get_branch_id(),
                        'grade_name' => $gradeData['name'],
                        'grade_range' => $gradeData['range'],
                        // Add other fields as necessary
                    ]);
            }
        }

        Alert::toast(_lang('Exam grades updated successfully.'), 'success');

        return redirect('semester-exam-settings-exam-startup');
    }

    public function examAssignStore(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'class_id' => 'required|integer',
            'exam_ids' => 'required|array',
            'exam_ids.*' => 'required|integer',
            'merit_process_type_id' => 'required|integer',
        ]);

        $instituteId = get_institute_id();
        $branchId = get_branch_id();
        $sessionId = get_option('academic_year');

        foreach ($request->exam_ids as $examId) {
            ClassExam::updateOrCreate(
                [
                    'institute_id' => $instituteId,
                    'branch_id' => $branchId,
                    'class_id' => $request->class_id,
                    'exam_id' => $examId,
                    'session_id' => $sessionId,
                ],
                [
                    'merit_process_type_id' => $request->merit_process_type_id,
                ]
            );
        }

        Alert::toast(_lang('Data saved successfully.'), 'success');

        return redirect('semester-exam-settings-exam-startup');
    }

    public function markInputSectionWise(): Factory|View
    {
        $classes = ClassModel::get();

        return view('examination::mark_config.mark_config_section_wise', compact('classes'));
    }

    /**
     * Subjects for the Mark Input form's Subject dropdown, filtered by the group the user
     * has currently selected in the Group dropdown (same "ungrouped + selected group" rule
     * markInputSectionWiseClass() applies server-side). Called via AJAX when Group changes so
     * a class whose subjects are all group-assigned isn't left with an empty, unusable
     * Subject dropdown on first load — the page previously had no way to update the subject
     * list without first submitting the whole form (BUG-007).
     */
    public function markInputSubjects(Request $request, int $classId): JsonResponse
    {
        $groupId = $request->filled('group_id') ? (int) $request->group_id : null;

        $subjects = Subject::where('class_id', $classId)
            ->where(function ($query) use ($groupId) {
                $query->whereNull('group_id');
                if ($groupId) {
                    $query->orWhere('group_id', $groupId);
                }
            })
            ->orderBy('subject_name')
            ->get(['id', 'subject_name']);

        return response()->json($subjects);
    }

    public function markInputSectionWiseClass(Request $request, int $classId): Factory|View
    {
        $examId = $request->filled('exam_id') ? (int) $request->exam_id : null;
        $groupId = $request->filled('group_id') ? (int) $request->group_id : null;
        $subjectId = $request->filled('subject_id') ? (int) $request->subject_id : null;
        $sectionId = $request->filled('section_id') ? (int) $request->section_id : null;
        $markConfig = collect();
        $students = collect();

        if ($examId && $subjectId) {
            $markConfigQuery = MarkConfig::with(['class', 'group', 'subject', 'exam', 'mark_config_exam_code'])
                ->where('class_id', $classId)
                ->where('exam_id', $examId)
                ->where('subject_id', $subjectId);

            if ($groupId) {
                $markConfigQuery->where('group_id', $groupId);
            }

            $markConfig = $markConfigQuery->get();

            if ($sectionId && $groupId) {
                $students = $this->studentService->getStudentsByClassSectionGroup(
                    $classId,
                    $sectionId,
                    $groupId,
                    200,
                    get_institute_id(),
                    get_branch_id()
                );
            } elseif ($sectionId) {
                $students = $this->studentService->getStudentsByClassSectionGroup(
                    $classId,
                    $sectionId,
                    0,
                    200,
                    get_institute_id(),
                    get_branch_id()
                );
            }
        }

        $class = ClassModel::where('id', $classId)->with('sections')->get();
        $subjects = Subject::where('class_id', $classId)
            ->where(function ($query) use ($groupId) {
                $query->whereNull('group_id');
                if ($groupId) {
                    $query->orWhere('group_id', $groupId);
                }
            })
            ->get();
        $classExams = ClassExam::where('class_id', $classId)->with('exam', 'class')->get();

        return view('examination::mark_config.mark_input_page', compact('class', 'subjects', 'classExams', 'classId', 'examId', 'groupId', 'subjectId', 'markConfig', 'students', 'sectionId'));
    }

    public function markStoreSectionWise(Request $request): Redirector|RedirectResponse
    {
        // Collect the basic details
        $classId = (int) $request->class_id;
        $groupId = $request->filled('group_id') ? (int) $request->group_id : null;
        $subjectId = (int) $request->subject_id;
        $examId = (int) $request->exam_id;

        try {
            // Start a database transaction
            DB::beginTransaction();

            // Loop through each student's marks
            foreach ($request->input('marks') as $studentId => $studentMarks) {
                // Define the identifying columns for the record
                $identifiers = [
                    'institute_id' => get_institute_id(),
                    'branch_id' => get_branch_id(),
                    'session_id' => get_option('academic_year'),
                    'student_id' => $studentId,
                    'class_id' => $classId,
                    'group_id' => $groupId,
                    'subject_id' => $subjectId,
                    'exam_id' => $examId,
                ];

                $this->markEntryService->saveSectionWiseMarks($identifiers, $studentMarks);
            }

            // Commit the transaction
            DB::commit();
            Alert::toast(_lang('Marks stored successfully.'), 'success');

            return redirect('mark-input-section-wise');
        } catch (\Exception $e) {
            // Roll back the transaction in case of any errors
            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return redirect('mark-input-section-wise');
        }
    }
}
