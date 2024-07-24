<?php

namespace Modules\Examination\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Academic\Models\Exam;
use Modules\Examination\Models\AssessmentDomain;
use Modules\Examination\Models\AssessmentItem;
use Modules\Examination\Models\StudentAssessment;
use Modules\Examination\Services\StudentAssessmentService;

class AssessmentDomainController extends Controller
{
    public function __construct(
        private readonly AcademicContextService $academicContext,
    ) {}

    public function index(): Factory|View
    {
        $this->authorizeManage();
        $domains = AssessmentDomain::with('items')->orderBy('display_order')->get();

        return view('examination::assessments.index', compact('domains'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManage();
        $data = $request->validate([
            'domain_type' => 'required|in:cognitive,affective,psychomotor',
            'name' => 'required|string|max:120',
            'display_order' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        AssessmentDomain::create([
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'session_id' => get_option('academic_year') ?: null,
            'domain_type' => $data['domain_type'],
            'name' => $data['name'],
            'display_order' => $data['display_order'] ?? 1,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', _lang('Assessment domain saved.'));
    }

    public function storeItem(Request $request, int $domainId): RedirectResponse
    {
        $this->authorizeManage();
        $domain = AssessmentDomain::findOrFail($domainId);
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'max_score' => 'required|numeric|min:0',
            'rating_type' => 'required|in:numeric,scale,grade',
            'grade_scale' => 'nullable|string',
            'display_order' => 'nullable|integer|min:1',
            'remarks_enabled' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $scale = null;
        if (! empty($data['grade_scale'])) {
            $scale = array_values(array_filter(array_map('trim', explode(',', $data['grade_scale']))));
        }

        AssessmentItem::create([
            'institute_id' => $domain->institute_id,
            'branch_id' => $domain->branch_id,
            'assessment_domain_id' => $domain->id,
            'name' => $data['name'],
            'max_score' => $data['max_score'],
            'rating_type' => $data['rating_type'],
            'grade_scale' => $scale,
            'display_order' => $data['display_order'] ?? 1,
            'remarks_enabled' => $request->boolean('remarks_enabled', true),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', _lang('Assessment item saved.'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorizeManage();
        AssessmentDomain::findOrFail($id)->delete();

        return back()->with('success', _lang('Assessment domain deleted.'));
    }

    public function entryForm(Request $request): Factory|View
    {
        $this->authorizeManage();
        $domains = AssessmentDomain::with(['items' => fn ($query) => $query->where('is_active', true)])
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        $students = collect();
        $existing = collect();
        if ($request->filled('class_id') && $request->filled('exam_id')) {
            $this->academicContext->assertClassSectionGroup(
                (int) $request->class_id,
                $request->filled('section_id') ? (int) $request->section_id : null,
            );

            $students = $this->academicContext
                ->studentSessionQuery(
                    (int) $request->class_id,
                    $request->filled('section_id') ? (int) $request->section_id : null,
                )
                ->with('student')
                ->orderBy('roll')
                ->get();
            $existing = StudentAssessment::where('exam_id', (int) $request->exam_id)
                ->whereIn('student_id', $students->pluck('student_id')->all() ?: [0])
                ->get()
                ->groupBy('student_id');
        }

        return view('examination::assessments.entry', [
            'domains' => $domains,
            'students' => $students,
            'existing' => $existing,
            'exams' => Exam::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['class_id', 'section_id', 'exam_id']),
        ]);
    }

    public function storeEntry(Request $request): RedirectResponse
    {
        $this->authorizeManage();
        $request->validate([
            'class_id' => 'required|integer',
            'section_id' => 'nullable|integer',
            'exam_id' => ['required', 'integer', app(AcademicContextService::class)->tenantExists('exams')],
            'entries' => 'required|array',
        ]);

        app(StudentAssessmentService::class)->save(
            (int) $request->class_id,
            $request->filled('section_id') ? (int) $request->section_id : null,
            (int) $request->exam_id,
            $request->entries,
        );

        return back()->with('success', _lang('Domain assessments saved.'));
    }

    private function authorizeManage(): void
    {
        abort_unless(
            auth()->user()?->can('exam_module_manage_assessment_domains')
            || auth()->user()?->can('exam_module_exam_start_up'),
            403
        );
    }
}
