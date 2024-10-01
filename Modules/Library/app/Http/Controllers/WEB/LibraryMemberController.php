<?php

namespace Modules\Library\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\Rule;
use Modules\Academic\Models\LibraryMember;
use Modules\Academic\Models\Section;
use Modules\Academic\Models\Staff;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\Teacher;
use Modules\Authentication\Models\User;
use Modules\Library\DataTables\LibraryMemberDataTable;

class LibraryMemberController extends Controller
{
    public function index(LibraryMemberDataTable $dataTable)
    {
        return $dataTable->render('library::members.member-list');
    }

    public function create(): Factory|View
    {
        return view('library::members.member-add', $this->formData());
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $userId = $this->validatedUserId($request);

        $member = new LibraryMember;
        $this->fillMember($member, $request->member_type, $userId);
        $member->save();

        return redirect('librarymembers-librarycard/'.$member->id)->with('success', _lang('Information has been added'));
    }

    public function show(int $id): Redirector|RedirectResponse
    {
        $find = LibraryMember::find($id);
        if (! $find) {
            return redirect('librarymembers')->with('error', _lang('Member not found'));
        }

        $user_id = User::find($find->user_id)?->id;
        if ($find->member_type == 'Student') {
            $id = Student::where('user_id', $user_id)->first()?->id;

            return $id ? redirect('students/'.$id) : redirect('librarymembers')->with('error', _lang('Student not found'));
        } elseif ($find->member_type == 'Teacher') {
            $id = Teacher::where('user_id', $user_id)->first()?->id;

            return $id ? redirect('teachers/'.$id) : redirect('librarymembers')->with('error', _lang('Teacher not found'));
        }

        $id = Staff::where('user_id', $user_id)->first()?->id;

        return $id ? redirect('staffs/'.$id) : redirect('librarymembers')->with('error', _lang('Staff not found'));
    }

    public function edit(int $id): Factory|View|Redirector|RedirectResponse
    {
        $member = LibraryMember::find($id);
        if (! $member) {
            return redirect('librarymembers')->with('error', _lang('Member not found'));
        }

        return view('library::members.member-add', $this->formData($member));
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $member = LibraryMember::find($id);
        if (! $member) {
            return redirect('librarymembers')->with('error', _lang('Member not found'));
        }

        $userId = $this->validatedUserId($request, $member->id);
        $this->fillMember($member, $request->member_type, $userId, keepLibraryId: true);
        $member->save();

        return redirect('librarymembers')->with('success', _lang('Information has been updated'));
    }

    public function library_card($id): Factory|View|Redirector|RedirectResponse
    {
        $library = LibraryMember::join('users', 'users.id', '=', 'library_members.user_id')
            ->where('library_members.id', $id)->first();

        if (! $library) {
            return redirect('librarymembers')->with('error', _lang('Member not found'));
        }

        return view('library::members.library-card', compact('library'));
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $member = LibraryMember::find($id);
        if (! $member) {
            return redirect('librarymembers')->with('error', _lang('Member not found'));
        }

        $member->delete();

        return redirect('librarymembers')->with('success', _lang('Information has been deleted'));
    }

    public function get_section(Request $request): string
    {
        $results = Section::where('class_id', $request->class_id)->orderBy('id', 'DESC')->get();
        $sections = '<option value="">'._lang('Select One').'</option>';
        foreach ($results as $data) {
            $sections .= '<option value="'.$data->id.'">'.e($data->section_name).'</option>';
        }

        return $sections;
    }

    public function get_student(Request $request): string
    {
        $results = Student::query()
            ->join('student_sessions', 'student_sessions.student_id', '=', 'students.id')
            ->where('student_sessions.session_id', get_option('academic_year'))
            ->where('student_sessions.institute_id', get_institute_id())
            ->where('student_sessions.branch_id', get_branch_id())
            ->where('student_sessions.section_id', intval($request->section_id))
            ->whereNull('student_sessions.deleted_at')
            ->select('students.user_id', 'students.first_name', 'students.last_name')
            ->orderBy('students.first_name')
            ->get();

        $students = '<option value="">'._lang('Select One').'</option>';
        foreach ($results as $data) {
            $name = trim($data->first_name.' '.$data->last_name);
            $students .= '<option value="'.$data->user_id.'">'.e($name).'</option>';
        }

        return $students;
    }

    private function formData(?LibraryMember $member = null): array
    {
        $teachers = Teacher::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->orderBy('name')
            ->get();

        $staffs = Staff::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->orderBy('name')
            ->get();

        $selectedClassId = old('class_id');
        $selectedSectionId = old('section_id');

        if ($member && $member->member_type === 'Student' && $member->student_id) {
            $student = Student::with('studentSession')->find($member->student_id);
            $selectedClassId = $selectedClassId ?: $student?->studentSession?->class_id;
            $selectedSectionId = $selectedSectionId ?: $student?->studentSession?->section_id;
        }

        return compact('teachers', 'staffs', 'member', 'selectedClassId', 'selectedSectionId');
    }

    private function validatedUserId(Request $request, ?int $ignoreMemberId = null): int
    {
        $request->validate([
            'member_type' => 'required|in:Student,Teacher,Staff',
            'student_user_id' => 'required_if:member_type,Student|nullable|integer',
            'teacher_user_id' => 'required_if:member_type,Teacher|nullable|integer',
            'staff_user_id' => 'required_if:member_type,Staff|nullable|integer',
        ]);

        $userId = match ($request->member_type) {
            'Student' => (int) $request->student_user_id,
            'Teacher' => (int) $request->teacher_user_id,
            default => (int) $request->staff_user_id,
        };

        $request->merge(['user_id' => $userId]);
        $request->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::unique('library_members', 'user_id')->ignore($ignoreMemberId),
            ],
        ]);

        return $userId;
    }

    private function fillMember(LibraryMember $member, string $memberType, int $userId, bool $keepLibraryId = false): void
    {
        $member->user_id = $userId;
        $member->member_type = $memberType;
        $member->student_id = null;
        $member->teacher_id = null;
        $member->staff_id = null;

        if ($memberType === 'Student') {
            $student = Student::where('user_id', $userId)->with('studentSession')->firstOrFail();
            $member->student_id = $student->id;
            if (! $keepLibraryId || ! $member->library_id) {
                $member->library_id = intval($student->studentSession?->roll);
            }
        } elseif ($memberType === 'Teacher') {
            $teacher = Teacher::where('user_id', $userId)
                ->where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->firstOrFail();
            $member->teacher_id = $teacher->id;
            if (! $keepLibraryId || ! $member->library_id) {
                $member->library_id = intval(date('Y').rand(100, 999).sprintf('%02d', $userId));
            }
        } else {
            $staff = Staff::where('user_id', $userId)
                ->where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->firstOrFail();
            $member->staff_id = $staff->id;
            if (! $keepLibraryId || ! $member->library_id) {
                $member->library_id = intval(date('Y').rand(100, 999).sprintf('%02d', $userId));
            }
        }
    }
}
