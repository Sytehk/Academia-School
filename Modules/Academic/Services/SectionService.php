<?php

declare(strict_types=1);

namespace Modules\Academic\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Academic\Models\Section;
use Modules\Academic\Repositories\SectionRepository;

class SectionService
{
    public function __construct(
        private readonly SectionRepository $sessionRepository
    ) {}

    public function getSections(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->sessionRepository->paginate($perPage, $filter);
    }

    public function findSectionById(int $id): ?Section
    {
        return $this->sessionRepository->show((int) $id);
    }

    public function createSection(array $data): ?Section
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->sessionRepository->create($data);
    }

    public function updateSection(array $data, int $id): mixed
    {
        return $this->sessionRepository->update($data, $id);
    }

    public function deleteSectionById(int $id): int
    {
        return $this->sessionRepository->delete($id);
    }
}
