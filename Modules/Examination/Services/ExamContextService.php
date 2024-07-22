<?php

namespace Modules\Examination\Services;

use App\Services\AcademicContextService;
use Modules\Academic\Models\Signature;
use Modules\Academic\Models\StudentSession;
use Modules\Authentication\Models\InstituteImageSetting;
use Modules\Examination\Models\ExamMark;

class ExamContextService
{
    public function __construct(
        private readonly AcademicContextService $academicContext,
    ) {}

    public function currentSessionId(): ?int
    {
        return $this->academicContext->currentSessionId();
    }

    public function normalizeGroupId(mixed $groupId): ?int
    {
        if ($groupId === null || $groupId === '' || (int) $groupId <= 0) {
            return null;
        }

        return (int) $groupId;
    }

    /**
     * Unique lookup keys aligned with exam_marks_unique_lookup index.
     *
     * @return array{institute_id: int, branch_id: int, session_id: int, student_id: int, exam_id: int, subject_id: int}
     */
    public function markLookupKeys(array $identifiers): array
    {
        return [
            'institute_id' => (int) $identifiers['institute_id'],
            'branch_id' => (int) $identifiers['branch_id'],
            'session_id' => (int) $identifiers['session_id'],
            'student_id' => (int) $identifiers['student_id'],
            'exam_id' => (int) $identifiers['exam_id'],
            'subject_id' => (int) $identifiers['subject_id'],
        ];
    }

    public function findExamMark(array $identifiers): ?ExamMark
    {
        return ExamMark::where($this->markLookupKeys($identifiers))->first();
    }

    /**
     * Resolve the canonical student session row for exam workflows.
     */
    public function resolveStudentSession(
        int $studentId,
        int $classId,
        ?int $sessionId = null,
        ?int $sectionId = null,
        ?int $groupId = null,
    ): ?StudentSession {
        $query = $this->academicContext->studentSessionQuery($classId, $sectionId, $groupId)
            ->where('student_id', $studentId);

        $sessionId = $sessionId ?: $this->currentSessionId();
        if ($sessionId) {
            $query->where('session_id', $sessionId);
        }

        return $query->with(['group', 'section'])->orderByDesc('id')->first();
    }

    /**
     * Apply academic-year and optional section/group filters to a student session query.
     */
    public function applySessionFilters($query, ?int $sectionId = null, ?int $groupId = null, bool $sessionAlreadyScoped = false): void
    {
        $this->academicContext->applyTenantScope($query);
        if (! $sessionAlreadyScoped) {
            $this->academicContext->applySessionScope($query);
        }

        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }

        $groupId = $this->normalizeGroupId($groupId);
        if ($groupId) {
            $query->where('group_id', $groupId);
        }
    }

    public function resolveGroupId(?int $preferredGroupId, ?StudentSession $session = null): ?int
    {
        return $this->normalizeGroupId($preferredGroupId) ?? $this->normalizeGroupId($session?->group_id);
    }

    public function instituteBranding(): array
    {
        $instituteId = get_institute_id();

        $imageSetting = InstituteImageSetting::query()
            ->where('status', 1)
            ->when($instituteId, fn ($query) => $query->where('institute_id', $instituteId))
            ->latest('id')
            ->first();

        $bannerPath = $imageSetting?->banner_image;
        $headerLogo = $imageSetting?->header_logo_light_theme ?: $imageSetting?->header_logo_dark_theme;

        return [
            'banner_image' => $bannerPath ? asset('uploads/institute_image_settings/'.$bannerPath) : null,
            'header_logo' => $headerLogo ? asset('uploads/institute_image_settings/'.$headerLogo) : null,
        ];
    }

    /**
     * Resolve teacher/principal signatures for result cards.
     *
     * @return array{class_teacher: ?array, principal: ?array}
     */
    public function resolveSignatures(array $settings = []): array
    {
        $instituteId = get_institute_id();
        $branchId = get_branch_id();

        $signatures = Signature::query()
            ->when($instituteId, fn ($query) => $query->where('institute_id', $instituteId))
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->get();

        $teacherId = $settings['teacher_signature_id'] ?? null;
        $principalId = $settings['principal_signature_id'] ?? null;

        $teacher = $teacherId
            ? $signatures->firstWhere('id', (int) $teacherId)
            : $this->matchSignature($signatures, ['class teacher', 'teacher', 'class_teacher']);

        $principal = $principalId
            ? $signatures->firstWhere('id', (int) $principalId)
            : $this->matchSignature($signatures, ['principal', 'head teacher', 'headteacher', 'head_teacher', 'headmaster']);

        return [
            'class_teacher' => $this->formatSignature($teacher),
            'principal' => $this->formatSignature($principal),
        ];
    }

    private function matchSignature($signatures, array $needles)
    {
        foreach ($signatures as $signature) {
            $haystack = strtolower(trim(($signature->place_at ?? '').' '.($signature->title ?? '')));
            foreach ($needles as $needle) {
                if (str_contains($haystack, strtolower($needle))) {
                    return $signature;
                }
            }
        }

        return $signatures->first();
    }

    private function formatSignature(?Signature $signature): ?array
    {
        if (! $signature) {
            return null;
        }

        return [
            'id' => $signature->id,
            'title' => $signature->title,
            'place_at' => $signature->place_at,
            'image' => $signature->image ? asset('uploads/'.$signature->image) : null,
        ];
    }
}
