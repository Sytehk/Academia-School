<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Academic\Models\Exam;
use Modules\Academic\Models\StudentSession;
use Modules\Academic\Services\ClassService;
use Modules\Academic\Services\SectionService;
use Modules\Academic\Services\StudentSessionService;

class ExamEssentialsController extends Controller
{
    public function __construct(
        private readonly ClassService $classService,
        private readonly SectionService $sectionService,
        private readonly StudentSessionService $studentSessionService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $classId = intval($request->class_id);
        $sectionId = intval($request->section_id);
        $examId = intval($request->exam_id);

        // Check if the exam exists
        $exam = Exam::find($examId);
        if (! $exam) {
            return $this->responseError([], 'Exam not found.');
        }

        // Base query to filter by class and section
        $query = StudentSession::where('class_id', $classId)
            ->where('section_id', $sectionId);

        // Fetch data with relationships
        $data = $query->with('student', 'session', 'class', 'section')->get();

        // Check for the requested type and respond accordingly
        if ($request->type === 'seat_plan') {
            return $this->responseSuccess([
                'type' => 'seat_plan',
                'exam_name' => $exam->name,
                'data' => $data,
            ], 'Seat plan data fetched successfully.');
        }

        if ($request->type === 'admit_card') {
            return $this->responseSuccess([
                'type' => 'admit_card',
                'exam_name' => $exam->name,
                'data' => $data,
            ], 'Admit card data fetched successfully.');
        }

        // Respond with error if the type does not match
        return $this->responseError([], 'Invalid request type.');
    }
}
