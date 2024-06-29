<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Academic\Models\Section;
use Modules\Academic\Models\Subject;
use RealRashid\SweetAlert\Facades\Alert;

class SectionController extends Controller
{
    public function __construct(
        private readonly AcademicContextService $academicContext,
    ) {}

    public function index($class = ''): Factory|View
    {
        $query = Section::select(
            'sections.*',
            'sections.id AS id',
            'teachers.name as teacher_name',
            'student_groups.group_name'
        )
            ->leftJoin('teachers', 'teachers.id', '=', 'sections.class_teacher_id')
            ->leftJoin('classes', 'classes.id', '=', 'sections.class_id')
            ->leftJoin('student_groups', 'sections.student_group_id', '=', 'student_groups.id')
            ->orderBy('sections.rank', 'ASC');

        if ($class != '') {
            $query->where('sections.class_id', (int) $class);
        }

        $sections = $query->get();

        return view('academic::sections.section-add', compact('sections', 'class'));
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'section_name' => 'required|string|max:191',
            'class_id' => 'required|array|min:1',
            'class_id.*' => 'required|integer',
            'student_group_id' => 'nullable',
            'class_teacher_id' => 'nullable|string|max:191',
        ]);

        $instituteId = get_institute_id();
        $branchId = get_branch_id();
        $classIds = array_unique($request->class_id);

        $created = 0;
        $skipped = 0;

        foreach ($classIds as $classId) {
            $this->academicContext->assertClassSectionGroup((int) $classId);

            $exists = Section::where('institute_id', $instituteId)
                ->where('branch_id', $branchId)
                ->where('class_id', $classId)
                ->where('section_name', $request->section_name)
                ->where('student_group_id', $request->student_group_id)
                ->exists();

            if ($exists) {
                $skipped++;

                continue;
            }

            $section = new Section;
            $section->institute_id = $instituteId;
            $section->branch_id = $branchId;
            $section->section_name = $request->section_name;
            $section->class_id = $classId;
            $section->class_teacher_id = $request->class_teacher_id;
            $section->room_no = $request->room_no;
            $section->rank = $request->rank;
            $section->capacity = $request->capacity;
            $section->student_group_id = $request->student_group_id;
            $section->save();

            $created++;
        }

        if ($created > 0 && $skipped > 0) {
            Alert::toast(_lang('Section created for').' '.$created.' '._lang('class(es); already existed in').' '.$skipped.' '._lang('class(es).'), 'success');
        } elseif ($created > 0) {
            Alert::toast(_lang('Section has been create successfully.'), 'success');
        } else {
            Alert::error('Error', _lang('This section already exists in the selected class(es).'));
        }

        return redirect('sections');
    }

    public function get_section(Request $request): string
    {
        $classId = (int) $request->class_id;

        return $this->academicContext->renderSectionOptions(
            $classId,
            null,
            $request->filled('group_id') ? (int) $request->group_id : null,
            false,
        );
    }

    public function get_section2(Request $request): string
    {
        $classId = (int) $request->class_id;
        $selectedId = $request->input('selected_section_id');

        return $this->academicContext->renderSectionOptions(
            $classId,
            $selectedId !== null && $selectedId !== '' ? (int) $selectedId : null,
            $request->filled('group_id') ? (int) $request->group_id : null,
            true,
        );
    }

    public function classWiseGroups(Request $request): string
    {
        $classId = (int) $request->query('class_id', 0);
        $sectionId = $request->query('section_id');
        $selectedId = $request->query('selected_group_id');

        return $this->academicContext->renderGroupOptions(
            $classId,
            $sectionId !== null && $sectionId !== '' ? (int) $sectionId : null,
            $selectedId !== null && $selectedId !== '' ? (int) $selectedId : null,
            true,
        );
    }

    public function classWiseSubjects(Request $request): string
    {
        $classId = (int) $request->class_id;
        if (! $this->academicContext->findOwnedClass($classId)) {
            return '<option value="" disabled>'.e(_lang('Invalid class')).'</option>';
        }

        $results = Subject::where('class_id', $classId)->get();
        $subjects = '<option value="" disabled>'.e(_lang('Select Subject')).'</option>';
        foreach ($results as $data) {
            $subjects .= '<option value="'.$data->id.'">'.e($data->subject_name.'-'.$data->subject_code).'</option>';
        }

        return $subjects;
    }

    public function fetchSections(Request $request): string
    {
        $classId = (int) $request->class_id;
        $groupId = (int) $request->group_id;

        return $this->academicContext->renderSectionOptions($classId, null, $groupId, false);
    }

    public function edit(int $id): Factory|View
    {
        $section = Section::select('*', 'sections.id AS id')
            ->join('classes', 'classes.id', '=', 'sections.class_id')
            ->where('sections.id', $id)
            ->firstOrFail();

        return view('academic::sections.section-edit', compact('section'));
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $request->validate([
            'section_name' => 'required|string|max:191',
            'class_id' => 'required',
            'student_group_id' => 'required',
        ]);

        $this->academicContext->assertClassSectionGroup((int) $request->class_id);

        $section = Section::findOrFail($id);
        $section->section_name = $request->section_name;
        $section->class_id = $request->class_id;
        $section->student_group_id = $request->student_group_id;
        $section->room_no = $request->room_no;
        $section->save();

        Alert::toast('Section has been update successfully.', 'success');

        return redirect('sections');
    }

    public function destroy(int $id): RedirectResponse|Redirector
    {
        $section = Section::findOrFail($id);

        if ($section->studentSessions()->exists()) {
            Alert::warning(
                'Warning',
                'This section cannot be deleted because students are assigned to it.'
            );

            return redirect()->back();
        }

        $section->delete();

        Alert::toast('Section has been deleted successfully.', 'success');

        return redirect('sections');
    }
}
