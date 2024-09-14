<?php

namespace Modules\Teacher\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Academic\Models\Assignment;
use Modules\Academic\Models\ClassRoutine;
use Modules\Academic\Models\Teacher;
use Modules\Teacher\Http\Controllers\Concerns\ScopesTeacherResources;

class TeacherController extends Controller
{
    use ScopesTeacherResources;

    public function my_profile(): Factory|View
    {
        $teacher = Teacher::select('*', 'teachers.id AS id')
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->where('teachers.id', get_teacher_id())
            ->first();

        return view('teacher::profile', compact('teacher'));
    }

    public function class_schedule(): Factory|View
    {
        $routine = ClassRoutine::getTeacherRoutine(get_teacher_id());

        return view('teacher::class_schedule', compact('routine'));
    }

    public function assignments(): Factory|View
    {
        $assignments = Assignment::select(
            'assignments.*',
            'classes.class_name',
            'sections.section_name',
            'subjects.subject_name'
        )
            ->join('classes', 'classes.id', '=', 'assignments.class_id')
            ->join('sections', 'sections.id', '=', 'assignments.section_id')
            ->join('subjects', 'subjects.id', '=', 'assignments.subject_id')
            ->join('assign_subjects', function ($join) {
                $join->on('assign_subjects.subject_id', '=', 'assignments.subject_id')
                    ->on('assign_subjects.section_id', '=', 'assignments.section_id');
            })
            ->where('assign_subjects.teacher_id', get_teacher_id())
            ->where('assignments.session_id', get_option('academic_year'))
            ->where('assignments.institute_id', get_institute_id())
            ->where('assignments.branch_id', get_branch_id())
            ->orderBy('assignments.id', 'DESC')
            ->get();

        return view('teacher::assignments.assignment-list', compact('assignments'));
    }

    public function create_assignment(): Factory|View
    {
        return view('teacher::assignments.assignment-add');
    }

    public function store_assignment(Request $request): Redirector|RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'class_id' => 'required|integer|exists:classes,id',
            'section_id' => 'required|integer|exists:sections,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'file' => 'required|file|max:10240',
            'file_2' => 'nullable|file|max:10240',
            'file_3' => 'nullable|file|max:10240',
            'file_4' => 'nullable|file|max:10240',
        ]);

        if (! $this->teacherTeaches((int) $validated['class_id'], (int) $validated['section_id'], (int) $validated['subject_id'])) {
            return back()->withInput()->with('error', _lang('You can only create assignments for a class, section and subject assigned to you.'));
        }

        $assignment = new Assignment;
        $assignment->institute_id = get_institute_id();
        $assignment->branch_id = get_branch_id();
        $assignment->session_id = get_option('academic_year');
        $assignment->created_by = $request->user()->id;
        $assignment->title = $validated['title'];
        $assignment->description = $validated['description'] ?? null;
        $assignment->deadline = $validated['deadline'];
        $assignment->class_id = $validated['class_id'];
        $assignment->section_id = $validated['section_id'];
        $assignment->subject_id = $validated['subject_id'];

        $this->applyAssignmentFiles($request, $assignment);

        $assignment->save();

        return redirect('teacher/assignments')->with('success', _lang('Information has been added'));
    }

    public function edit_assignment($id): Factory|View|RedirectResponse
    {
        $assignment = $this->findOwnedAssignment((int) $id);

        if (! $assignment) {
            return redirect('teacher/assignments')->with('error', _lang('Assignment not found.'));
        }

        return view('teacher::assignments.assignment-edit', compact('assignment'));
    }

    public function show_assignment($id): Factory|View|RedirectResponse
    {
        $assignment = $this->findOwnedAssignment((int) $id);

        if (! $assignment) {
            return redirect('teacher/assignments')->with('error', _lang('Assignment not found.'));
        }

        $assignment->loadMissing('subject');

        return view('teacher::assignments.assignment-show', compact('assignment'));
    }

    public function update_assignment(Request $request, int $id): Redirector|RedirectResponse
    {
        $assignment = $this->findOwnedAssignment($id);

        if (! $assignment) {
            return redirect('teacher/assignments')->with('error', _lang('Assignment not found.'));
        }

        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
            'class_id' => 'required|integer|exists:classes,id',
            'section_id' => 'required|integer|exists:sections,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'file' => 'nullable|file|max:10240',
            'file_2' => 'nullable|file|max:10240',
            'file_3' => 'nullable|file|max:10240',
            'file_4' => 'nullable|file|max:10240',
        ]);

        if (! $this->teacherTeaches((int) $validated['class_id'], (int) $validated['section_id'], (int) $validated['subject_id'])) {
            return back()->withInput()->with('error', _lang('You can only move an assignment to a class, section and subject assigned to you.'));
        }

        $assignment->session_id = get_option('academic_year');
        $assignment->title = $validated['title'];
        $assignment->description = $validated['description'] ?? null;
        $assignment->deadline = $validated['deadline'];
        $assignment->class_id = $validated['class_id'];
        $assignment->section_id = $validated['section_id'];
        $assignment->subject_id = $validated['subject_id'];

        $this->applyAssignmentFiles($request, $assignment);

        $assignment->save();

        return redirect('teacher/assignments')->with('success', _lang('Information has been updated'));
    }

    public function destroy_assignment($id): Redirector|RedirectResponse
    {
        $assignment = $this->findOwnedAssignment((int) $id);

        if (! $assignment) {
            return redirect('teacher/assignments')->with('error', _lang('Assignment not found.'));
        }

        $assignment->delete();

        return redirect('teacher/assignments')->with('success', _lang('Information has been deleted'));
    }

    private function applyAssignmentFiles(Request $request, Assignment $assignment): void
    {
        foreach (['file', 'file_2', 'file_3', 'file_4'] as $field) {
            if ($request->hasFile($field)) {
                $assignment->{$field} = secure_public_move($request->file($field), 'uploads/files/assignments/');
            }
        }
    }
}
