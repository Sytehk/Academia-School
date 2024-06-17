<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\ValidationException;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Section;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\StudentGroup;
use Modules\Academic\Models\StudentSession;

/**
 * Central institute → branch → session → class → section → group context helpers.
 * Use for query scoping and server-side validation of submitted foreign keys.
 */
class AcademicContextService
{
    public function findStudentSessionByQrCode(string $qrCode): ?StudentSession
    {
        $query = $this->applyTenantScope(StudentSession::query())
            ->where('qr_code', $qrCode);

        $this->applySessionScope($query);

        return $query->first();
    }

    /**
     * @throws ValidationException
     */
    public function assertStudentsInClassSection(array $studentIds, int $classId, ?int $sectionId = null, ?int $groupId = null): void
    {
        $studentIds = array_values(array_unique(array_filter(array_map('intval', $studentIds))));

        if ($studentIds === []) {
            return;
        }

        $this->assertClassSectionGroup($classId, $sectionId, $groupId);

        $validCount = $this->studentSessionQuery($classId, $sectionId, $groupId)
            ->whereIn('student_id', $studentIds)
            ->distinct()
            ->count('student_id');

        if ($validCount !== count($studentIds)) {
            throw ValidationException::withMessages([
                'student_ids' => [_lang('One or more students are invalid for the selected class, section, and session.')],
            ]);
        }
    }

    public function currentSessionId(): ?int
    {
        $sessionId = get_option('academic_year');

        return $sessionId ? (int) $sessionId : null;
    }

    public function instituteId(): ?int
    {
        $id = get_institute_id();

        return $id ? (int) $id : null;
    }

    public function branchId(): ?int
    {
        $id = get_branch_id();

        return $id ? (int) $id : null;
    }

    public function applyTenantScope(Builder $query, ?string $table = null): Builder
    {
        $table = $table ?? $query->getModel()->getTable();

        if ($instituteId = $this->instituteId()) {
            $query->where($table.'.institute_id', $instituteId);
        }

        if ($branchId = $this->branchId()) {
            $query->where($table.'.branch_id', $branchId);
        }

        return $query;
    }

    public function applySessionScope(Builder $query, string $column = 'session_id'): Builder
    {
        if ($sessionId = $this->currentSessionId()) {
            $query->where($column, $sessionId);
        }

        return $query;
    }

    public function findOwnedClass(int $classId): ?ClassModel
    {
        return $this->applyTenantScope(ClassModel::query())
            ->where('id', $classId)
            ->first();
    }

    public function findOwnedSection(int $sectionId, ?int $classId = null): ?Section
    {
        $query = $this->applyTenantScope(Section::query())->where('id', $sectionId);

        if ($classId !== null) {
            $query->where('class_id', $classId);
        }

        return $query->first();
    }

    public function findOwnedGroup(int $groupId): ?StudentGroup
    {
        return $this->applyTenantScope(StudentGroup::query())
            ->where('id', $groupId)
            ->first();
    }

    public function findOwnedStudentSession(int $studentSessionId): ?StudentSession
    {
        return $this->applyTenantScope(StudentSession::query())
            ->where('id', $studentSessionId)
            ->first();
    }

    public function studentSessionForStudent(int $studentId): ?StudentSession
    {
        $query = $this->applyTenantScope(StudentSession::query())
            ->where('student_id', $studentId);

        $this->applySessionScope($query);

        return $query->orderByDesc('id')->first();
    }

    /**
     * @throws ValidationException
     */
    public function assertClassSectionGroup(?int $classId, ?int $sectionId = null, ?int $groupId = null): void
    {
        if ($classId === null) {
            return;
        }

        if (! $this->findOwnedClass($classId)) {
            throw ValidationException::withMessages([
                'class_id' => [_lang('The selected class is invalid for your institute/branch.')],
            ]);
        }

        if ($sectionId !== null && $sectionId > 0) {
            if (! $this->findOwnedSection($sectionId, $classId)) {
                throw ValidationException::withMessages([
                    'section_id' => [_lang('The selected section does not belong to the chosen class.')],
                ]);
            }
        }

        if ($groupId !== null && $groupId > 0) {
            if (! $this->groupBelongsToClassSection($groupId, $classId, $sectionId)) {
                throw ValidationException::withMessages([
                    'group_id' => [_lang('The selected group is invalid for the chosen class/section.')],
                ]);
            }
        }
    }

    /**
     * @throws ValidationException
     */
    public function assertOwnedStudentSession(int $studentSessionId): StudentSession
    {
        $session = $this->findOwnedStudentSession($studentSessionId);

        if (! $session) {
            throw ValidationException::withMessages([
                'student_session_id' => [_lang('The selected student session is invalid for your context.')],
            ]);
        }

        $currentSessionId = $this->currentSessionId();
        if ($currentSessionId && (int) $session->session_id !== $currentSessionId) {
            throw ValidationException::withMessages([
                'student_session_id' => [_lang('The selected student does not belong to the active academic session.')],
            ]);
        }

        return $session;
    }

    public function sectionsForClass(int $classId, ?int $groupId = null): Collection
    {
        if ($classId <= 0 || ! $this->findOwnedClass($classId)) {
            return new Collection;
        }

        $query = $this->applyTenantScope(Section::query())
            ->where('class_id', $classId)
            ->orderBy('rank');

        if ($groupId !== null && $groupId > 0) {
            $query->where('student_group_id', $groupId);
        }

        return $query->get(['id', 'class_id', 'section_name', 'student_group_id']);
    }

    public function groupsForClassSection(int $classId, ?int $sectionId = null): Collection
    {
        if ($classId <= 0 || ! $this->findOwnedClass($classId)) {
            return new Collection;
        }

        $groupIds = $this->studentSessionQuery($classId, $sectionId)
            ->whereNotNull('group_id')
            ->where('group_id', '>', 0)
            ->distinct()
            ->pluck('group_id');

        if ($sectionId !== null && $sectionId > 0) {
            $sectionGroupId = Section::query()
                ->where('id', $sectionId)
                ->where('class_id', $classId)
                ->value('student_group_id');

            if ($sectionGroupId) {
                $groupIds = $groupIds->push((int) $sectionGroupId)->unique()->values();
            }
        }

        if ($groupIds->isEmpty()) {
            return new Collection;
        }

        return $this->applyTenantScope(StudentGroup::query())
            ->whereIn('id', $groupIds->all())
            ->orderBy('group_name')
            ->get(['id', 'group_name']);
    }

    public function studentSessionQuery(int $classId, ?int $sectionId = null, ?int $groupId = null): Builder
    {
        $query = $this->applyTenantScope(StudentSession::query())
            ->where('class_id', $classId);

        $this->applySessionScope($query);

        if ($sectionId !== null && $sectionId > 0) {
            $query->where('section_id', $sectionId);
        }

        if ($groupId !== null && $groupId > 0) {
            $query->where('group_id', $groupId);
        }

        return $query;
    }

    public function groupBelongsToClassSection(int $groupId, int $classId, ?int $sectionId = null): bool
    {
        if (! $this->findOwnedGroup($groupId)) {
            return false;
        }

        $existsInSessions = $this->studentSessionQuery($classId, $sectionId, $groupId)->exists();
        if ($existsInSessions) {
            return true;
        }

        if ($sectionId !== null && $sectionId > 0) {
            return Section::query()
                ->where('id', $sectionId)
                ->where('class_id', $classId)
                ->where('student_group_id', $groupId)
                ->exists();
        }

        return Section::query()
            ->where('class_id', $classId)
            ->where('student_group_id', $groupId)
            ->exists();
    }

    public function renderSectionOptions(int $classId, ?int $selectedId = null, ?int $groupId = null, bool $includeAll = true): string
    {
        $sections = $this->sectionsForClass($classId, $groupId);
        $html = $includeAll
            ? '<option value="">'.e(_lang('All Sections')).'</option>'
            : '<option value="">'.e(_lang('Select One')).'</option>';

        foreach ($sections as $section) {
            $selected = (int) $selectedId === (int) $section->id ? ' selected' : '';
            $html .= '<option value="'.$section->id.'"'.$selected.'>'.e($section->section_name).'</option>';
        }

        return $html;
    }

    public function renderGroupOptions(int $classId, ?int $sectionId = null, ?int $selectedId = null, bool $includeAll = true): string
    {
        $groups = $this->groupsForClassSection($classId, $sectionId);
        $html = $includeAll
            ? '<option value="">'.e(_lang('All Groups')).'</option>'
            : '<option value="">'.e(_lang('Select One')).'</option>';

        foreach ($groups as $group) {
            $selected = (int) $selectedId === (int) $group->id ? ' selected' : '';
            $html .= '<option value="'.$group->id.'"'.$selected.'>'.e($group->group_name).'</option>';
        }

        return $html;
    }

    /** Scoped exists rule for tenant-owned tables. */
    public function tenantExists(string $table, string $column = 'id'): Exists
    {
        return Rule::exists($table, $column)->where(function ($query) {
            if ($instituteId = $this->instituteId()) {
                $query->where('institute_id', $instituteId);
            }
            if ($branchId = $this->branchId()) {
                $query->where('branch_id', $branchId);
            }
        });
    }

    public function findOwnedStudent(int $studentId): ?Student
    {
        return $this->applyTenantScope(Student::query())
            ->where('id', $studentId)
            ->first();
    }

    /**
     * Active academic session for the current tenant, or null if unset.
     */
    public function requireSessionId(): ?int
    {
        return $this->currentSessionId();
    }

    /**
     * Scope a raw query builder table alias by institute/branch.
     */
    public function applyTenantScopeToTable($query, string $table): void
    {
        if ($instituteId = $this->instituteId()) {
            $query->where($table.'.institute_id', $instituteId);
        }
        if ($branchId = $this->branchId()) {
            $query->where($table.'.branch_id', $branchId);
        }
    }
}
