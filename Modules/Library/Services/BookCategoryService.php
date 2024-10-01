<?php

declare(strict_types=1);

namespace Modules\Library\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Library\Models\BookCategory;
use Modules\Library\Repositories\BookCategoryRepository;

class BookCategoryService
{
    public function __construct(
        private readonly BookCategoryRepository $bookCategoryRepository
    ) {}

    public function getBookCategories(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->bookCategoryRepository->paginate($perPage, $filter);
    }

    public function findBookCategoryById(int $id): ?BookCategory
    {
        return $this->bookCategoryRepository->show((int) $id);
    }

    public function createBookCategory(array $data): ?BookCategory
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->bookCategoryRepository->create($data);
    }

    public function updateBookCategory(array $data, int $id): mixed
    {
        return $this->bookCategoryRepository->update($data, $id);
    }

    public function deleteBookCategoryById(int $id): int
    {
        return $this->bookCategoryRepository->delete($id);
    }
}
