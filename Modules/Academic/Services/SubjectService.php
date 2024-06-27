<?php

declare(strict_types=1);

namespace Modules\Academic\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Academic\Models\Subject;
use Modules\Academic\Repositories\SubjectRepository;

class SubjectService
{
    public function __construct(
        private readonly SubjectRepository $subjectRepository
    ) {}

    public function getSubjects(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->subjectRepository->paginate($perPage, $filter);
    }

    public function findSubjectById(int $id): ?Subject
    {
        return $this->subjectRepository->show((int) $id);
    }

    public function createSubject(array $data): ?Subject
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->subjectRepository->create($data);
    }

    public function updateSubject(array $data, int $id): mixed
    {
        return $this->subjectRepository->update($data, $id);
    }

    public function deleteSubjectById(int $id): int
    {
        return $this->subjectRepository->delete($id);
    }

    public function getSubjectsByClassId(int $id): Collection
    {
        return Subject::where('class_id', $id)->get();
    }
}
