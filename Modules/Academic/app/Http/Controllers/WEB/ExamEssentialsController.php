<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Modules\Academic\Models\StudentSession;
use Modules\Academic\Services\ClassService;
use Modules\Academic\Services\SectionService;

class ExamEssentialsController extends Controller
{
    public function __construct(
        private readonly ClassService $classService,
        private readonly SectionService $sectionService
    ) {}

    public function index(): Factory|View
    {
        return view('academic::exam_essentials.search', [
            'classes' => $this->classService->getClasses(),
            'sections' => $this->sectionService->getSections(),
        ]);
    }

    public function store(Request $request)
    {
        $classId = $request->class_id;
        $sectionId = $request->section_id;
        $fromRoll = $request->from_roll;
        $toRoll = $request->to_roll;

        $query = StudentSession::where('class_id', $classId)->where('section_id', $sectionId);

        if ($fromRoll !== null && $toRoll !== null) {
            $query->whereBetween('roll', [$fromRoll, $toRoll]);
        }

        $data = $query->with('student', 'session', 'class', 'section', 'group')->get();

        if ($request->seat_plan === '1') {
            return view('academic::exam_essentials.seat_plan', compact('data'));
        }

        if ($request->admit_card === '1') {
            return view('academic::exam_essentials.admit_card', compact('data'));
        }
    }
}
