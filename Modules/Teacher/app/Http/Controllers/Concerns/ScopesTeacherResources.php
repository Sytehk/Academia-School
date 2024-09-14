<?php

namespace Modules\Teacher\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\Assignment;

/**
 * Authorization helpers shared by the Teacher portal (WEB + API) controllers.
 *
 * A teacher may only act on classes / sections / subjects that are actually
 * assigned to them. The authoritative mapping is `assign_subjects`
 * (teacher_id, subject_id, section_id) — which is unique on (subject_id,
 * section_id), so each subject+section belongs to exactly one teacher — plus
 * `class_assigns` (teacher_id, class_id) for class-level features.
 */
trait ScopesTeacherResources
{
    protected function currentTeacherId(): ?int
    {
        $userId = Auth::id();
        if (! $userId) {
            return null;
        }

        return DB::table('teachers')
            ->where('user_id', $userId)
            ->value('id');
    }

    /**
     * [[subject_id, section_id], …] the current teacher is assigned to teach.
     *
     * @return array<int, array{0:int,1:int}>
     */
    protected function assignedSubjectSectionPairs(): array
    {
        $teacherId = $this->currentTeacherId();
        if (! $teacherId) {
            return [];
        }

        return DB::table('assign_subjects')
            ->where('teacher_id', $teacherId)
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->get(['subject_id', 'section_id'])
            ->map(fn ($r) => [(int) $r->subject_id, (int) $r->section_id])
            ->all();
    }

    /** Class ids the current teacher is assigned to (assign_subjects ∪ class_assigns). */
    protected function assignedClassIds(): array
    {
        $teacherId = $this->currentTeacherId();
        if (! $teacherId) {
            return [];
        }

        $fromSubjects = DB::table('assign_subjects')
            ->join('subjects', 'subjects.id', '=', 'assign_subjects.subject_id')
            ->where('assign_subjects.teacher_id', $teacherId)
            ->pluck('subjects.class_id');

        $fromClasses = DB::table('class_assigns')
            ->where('teacher_id', $teacherId)
            ->pluck('class_id');

        return $fromSubjects->merge($fromClasses)->map(fn ($id) => (int) $id)->unique()->values()->all();
    }

    /** True when the given class+section+subject triple is assigned to this teacher. */
    protected function teacherTeaches(int $classId, int $sectionId, int $subjectId): bool
    {
        $teacherId = $this->currentTeacherId();
        if (! $teacherId) {
            return false;
        }

        return DB::table('assign_subjects')
            ->join('subjects', 'subjects.id', '=', 'assign_subjects.subject_id')
            ->where('assign_subjects.teacher_id', $teacherId)
            ->where('assign_subjects.subject_id', $subjectId)
            ->where('assign_subjects.section_id', $sectionId)
            ->where('subjects.class_id', $classId)
            ->exists();
    }

    /**
     * An Assignment query already constrained to what the current teacher owns:
     * either they created it, or it targets a subject+section assigned to them
     * (covers rows created before `created_by` existed / by an admin).
     */
    protected function teacherAssignmentQuery(): Builder
    {
        $userId = Auth::id();
        $pairs = $this->assignedSubjectSectionPairs();

        return Assignment::query()
            ->where('assignments.session_id', get_option('academic_year'))
            ->where(function (Builder $q) use ($userId, $pairs) {
                $q->where('assignments.created_by', $userId);

                foreach ($pairs as [$subjectId, $sectionId]) {
                    $q->orWhere(function (Builder $inner) use ($subjectId, $sectionId) {
                        $inner->where('assignments.subject_id', $subjectId)
                            ->where('assignments.section_id', $sectionId);
                    });
                }
            });
    }

    protected function findOwnedAssignment(int $id): ?Assignment
    {
        return $this->teacherAssignmentQuery()->whereKey($id)->first();
    }

    /**
     * True when the teacher may act on this section: either they are its
     * homeroom/class teacher (sections.class_teacher_id), or they teach at
     * least one subject in it (assign_subjects). Used to scope QR attendance
     * scanning to sections a teacher is actually responsible for.
     */
    protected function teacherOwnsSection(int $sectionId): bool
    {
        $teacherId = $this->currentTeacherId();
        if (! $teacherId) {
            return false;
        }

        $isClassTeacher = DB::table('sections')
            ->where('id', $sectionId)
            ->where('class_teacher_id', $teacherId)
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->exists();

        if ($isClassTeacher) {
            return true;
        }

        return DB::table('assign_subjects')
            ->where('teacher_id', $teacherId)
            ->where('section_id', $sectionId)
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->exists();
    }

    /**
     * [[class_id, section_id, class_name, section_name], …] every section this
     * teacher may act on (class-teacher or subject-teacher), for populating a
     * class/section picker without exposing sections that belong to other
     * teachers.
     *
     * @return array<int, array{class_id:int, section_id:int, class_name:string, section_name:string}>
     */
    protected function teacherOwnedSections(): array
    {
        $teacherId = $this->currentTeacherId();
        if (! $teacherId) {
            return [];
        }

        $asClassTeacher = DB::table('sections')
            ->join('classes', 'classes.id', '=', 'sections.class_id')
            ->where('sections.class_teacher_id', $teacherId)
            ->where('sections.institute_id', get_institute_id())
            ->where('sections.branch_id', get_branch_id())
            ->select('sections.id as section_id', 'sections.class_id', 'sections.section_name', 'classes.class_name');

        $asSubjectTeacher = DB::table('assign_subjects')
            ->join('sections', 'sections.id', '=', 'assign_subjects.section_id')
            ->join('classes', 'classes.id', '=', 'sections.class_id')
            ->where('assign_subjects.teacher_id', $teacherId)
            ->where('assign_subjects.institute_id', get_institute_id())
            ->where('assign_subjects.branch_id', get_branch_id())
            ->select('sections.id as section_id', 'sections.class_id', 'sections.section_name', 'classes.class_name');

        return $asClassTeacher->union($asSubjectTeacher)
            ->get()
            ->unique('section_id')
            ->map(fn ($row) => [
                'class_id' => (int) $row->class_id,
                'section_id' => (int) $row->section_id,
                'class_name' => $row->class_name,
                'section_name' => $row->section_name,
            ])
            ->values()
            ->all();
    }
}
