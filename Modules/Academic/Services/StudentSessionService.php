<?php

declare(strict_types=1);

namespace Modules\Academic\Services;

use App\Services\AcademicContextService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Academic\Models\StudentSession;
use Modules\Academic\Repositories\StudentSessionRepository;

class StudentSessionService
{
    public function __construct(
        private readonly StudentSessionRepository $studentSessionRepository,
        private readonly AcademicContextService $academicContext,
    ) {}

    public function getAllStudentSessions()
    {
        return $this->studentSessionRepository->all();
    }

    public function getStudentSessions(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->studentSessionRepository->paginate($perPage, $filter);
    }

    public function findStudentSessionById(int $id): ?StudentSession
    {
        return $this->studentSessionRepository->show((int) $id);
    }

    public function createStudentSession(array $data): ?StudentSession
    {
        $classId = (int) ($data['class_id'] ?? 0);
        $sectionId = (int) ($data['section_id'] ?? 0);
        $groupId = isset($data['group_id']) ? (int) $data['group_id'] : null;

        $this->academicContext->assertClassSectionGroup($classId, $sectionId, $groupId ?: null);

        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();
        $data['session_id'] = $data['session_id'] ?? $this->academicContext->currentSessionId();

        return $this->studentSessionRepository->create($data);
    }

    public function updateStudentSession(array $data, int $id): mixed
    {
        if (isset($data['class_id'], $data['section_id'])) {
            $groupId = isset($data['group_id']) ? (int) $data['group_id'] : null;
            $this->academicContext->assertClassSectionGroup(
                (int) $data['class_id'],
                (int) $data['section_id'],
                $groupId ?: null,
            );
        }

        return $this->studentSessionRepository->update($data, $id);
    }

    public function findStudentSessionByStudentId(int $id): ?StudentSession
    {
        return $this->studentSessionRepository->findStudentSessionByStudentId($id);
    }

    public function deleteStudentSessionById(int $id): int
    {
        return $this->studentSessionRepository->delete($id);
    }

    public function getStudentByClassId(int $id): Collection
    {
        return $this->studentSessionRepository->studentByClassId($id);
    }

    public function deleteStudentSessionByRollNo(int $studentId, int $rollNo)
    {
        return $this->studentSessionRepository->deleteByRoll($studentId, $rollNo);
    }

    public function generateUniqueQrCode(?int $ignoreSessionId = null): string
    {
        do {
            $code = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        } while ($this->studentSessionRepository->qrCodeExists($code, $ignoreSessionId));

        return $code;
    }

    public function resolveQrCode(?string $requested, ?int $ignoreSessionId = null): string
    {
        if (! empty($requested) && ! $this->studentSessionRepository->qrCodeExists($requested, $ignoreSessionId)) {
            return $requested;
        }

        return $this->generateUniqueQrCode($ignoreSessionId);
    }
}
