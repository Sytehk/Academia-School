<?php

namespace Modules\Examination\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Modules\Examination\Models\OnlineExam;
use Modules\Teacher\Http\Controllers\Concerns\ScopesTeacherResources;

/**
 * Who may manage which online exam.
 *
 * Back-office users reach the admin routes only through their `access:`
 * permission gate and may manage every exam of their tenant. A Teacher (portal
 * routes) may manage only exams for a subject assigned to them in
 * `assign_subjects` — for a section-specific exam, that exact section.
 */
class OnlineExamAccess
{
    use ScopesTeacherResources;

    public function isTeacher(): bool
    {
        return auth()->user()?->user_type === 'Teacher';
    }

    public function scope(Builder $query): Builder
    {
        if (! $this->isTeacher()) {
            return $query;
        }

        $pairs = $this->assignedSubjectSectionPairs();
        if ($pairs === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $outer) use ($pairs) {
            foreach ($pairs as [$subjectId, $sectionId]) {
                $outer->orWhere(function (Builder $q) use ($subjectId, $sectionId) {
                    $q->where('subject_id', $subjectId)
                        ->where(fn (Builder $s) => $s->whereNull('section_id')->orWhere('section_id', $sectionId));
                });
            }
        });
    }

    public function canManage(OnlineExam $exam): bool
    {
        return $this->canManageContext((int) $exam->class_id, (int) $exam->subject_id, $exam->section_id ? (int) $exam->section_id : null);
    }

    public function canManageContext(int $classId, int $subjectId, ?int $sectionId): bool
    {
        if (! $this->isTeacher()) {
            return true;
        }

        if ($sectionId) {
            return $this->teacherTeaches($classId, $sectionId, $subjectId);
        }

        $teacherId = $this->currentTeacherId();

        return $teacherId && DB::table('assign_subjects')
            ->join('subjects', 'subjects.id', '=', 'assign_subjects.subject_id')
            ->where('assign_subjects.teacher_id', $teacherId)
            ->where('assign_subjects.subject_id', $subjectId)
            ->where('subjects.class_id', $classId)
            ->exists();
    }

    /** Admin routes check permissions at the gate; teachers may do everything for their own exams. */
    public function can(string $permission): bool
    {
        return $this->isTeacher() || can_access_system($permission);
    }
}
