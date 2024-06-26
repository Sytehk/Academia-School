<?php

declare(strict_types=1);

namespace Modules\Academic\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Academic\Models\StudentSession;

class StudentSessionRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return StudentSession::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->get();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = StudentSession::query();

        return $query->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create(array $data): ?StudentSession
    {
        return StudentSession::create($data);
    }

    public function update(array $data, int $id): ?bool
    {
        $session = $this->show((int) $id);

        if (! $session) {
            return null;
        }

        return $session->update($data);
    }

    public function findStudentSessionByStudentId(int $id): ?StudentSession
    {
        // Scoped to the current academic session — a student can have a StudentSession
        // row per session, so an unscoped lookup can silently return the wrong one.
        return StudentSession::where('student_id', $id)
            ->where('session_id', get_option('academic_year'))
            ->first();
    }

    public function delete(int $id): int
    {
        return StudentSession::destroy($id);
    }

    public function show(int $id): ?StudentSession
    {
        return StudentSession::with('student')->find($id);
    }

    public function studentByClassId(int $id): Collection
    {
        $query = StudentSession::where('class_id', $id);

        if ($sessionId = get_option('academic_year')) {
            $query->where('session_id', $sessionId);
        }

        return $query->get();
    }

    public function deleteByRoll(int $studentId, int $rollNo): mixed
    {
        $query = StudentSession::where('student_id', $studentId)->where('roll', $rollNo);

        if ($sessionId = get_option('academic_year')) {
            $query->where('session_id', $sessionId);
        }

        return $query->first()?->delete();
    }

    public function qrCodeExists(string $qrCode, ?int $ignoreId = null): bool
    {
        return StudentSession::withTrashed()
            ->where('institute_id', get_institute_id())
            ->where('qr_code', $qrCode)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();
    }
}
