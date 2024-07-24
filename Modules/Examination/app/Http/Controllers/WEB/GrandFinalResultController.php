<?php

namespace Modules\Examination\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Academic\Models\StudentSession;
use Modules\Academic\Services\ClassService;
use Modules\Examination\Services\GrandFinalResultService;
use Pdf;

class GrandFinalResultController extends Controller
{
    public function __construct(
        private readonly ClassService $classService,
        private readonly AcademicContextService $academicContext,
        private readonly GrandFinalResultService $grandFinalResultService,
    ) {}

    public function index(): Factory|View
    {
        return view('examination::grand_final_result.search', [
            'classes' => $this->classService->getClasses(),
            'sections' => collect(),
        ]);
    }

    public function search(Request $request): Factory|View|RedirectResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $this->academicContext->assertClassSectionGroup(
            (int) $request->class_id,
            $request->filled('section_id') ? (int) $request->section_id : null,
        );

        $studentSessions = $this->filteredStudentSessions($request)->get();

        return view('examination::grand_final_result.select_students', [
            'studentSessions' => $studentSessions,
            'class_id' => $request->class_id,
            'section_id' => $request->section_id,
            'from_roll' => $request->from_roll,
            'to_roll' => $request->to_roll,
        ]);
    }

    public function preview(Request $request): Factory|View
    {
        $studentSessions = $this->selectedStudentSessions($request);
        $results = $this->grandFinalResultService->calculateForStudents($studentSessions);

        return view('examination::grand_final_result.result_view', compact('results'));
    }

    public function downloadPdf(Request $request): Response
    {
        $studentSessions = $this->selectedStudentSessions($request);
        $results = $this->grandFinalResultService->calculateForStudents($studentSessions);

        $pdf = Pdf::loadView('examination::grand_final_result.result_pdf', compact('results'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('grand-final-result-'.now()->format('Y-m-d').'.pdf');
    }

    private function filteredStudentSessions(Request $request)
    {
        $query = $this->academicContext
            ->studentSessionQuery(
                (int) $request->class_id,
                $request->filled('section_id') ? (int) $request->section_id : null,
            )
            ->with('student', 'session', 'class', 'section', 'group');

        if ($request->from_roll !== null && $request->from_roll !== '' && $request->to_roll !== null && $request->to_roll !== '') {
            $fromRoll = (int) $request->from_roll;
            $toRoll = (int) $request->to_roll;
            $query->whereRaw('CAST(roll AS UNSIGNED) BETWEEN ? AND ?', [$fromRoll, $toRoll]);
        }

        return $query->orderBy('roll');
    }

    private function selectedStudentSessions(Request $request)
    {
        $request->validate([
            'student_session_ids' => 'required|array|min:1',
            'student_session_ids.*' => 'integer',
        ]);

        $ids = array_map('intval', $request->student_session_ids);
        $sessions = StudentSession::whereIn('id', $ids)
            ->with('student', 'session', 'class', 'section', 'group')
            ->orderBy('roll')
            ->get();

        if ($sessions->count() !== count($ids)) {
            abort(422, _lang('One or more selected students are invalid for your context.'));
        }

        $currentSessionId = $this->academicContext->currentSessionId();
        if ($currentSessionId && $sessions->contains(fn ($session) => (int) $session->session_id !== $currentSessionId)) {
            abort(422, _lang('Selected students must belong to the active academic session.'));
        }

        return $sessions;
    }
}
