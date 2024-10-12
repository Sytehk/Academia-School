<?php

declare(strict_types=1);

namespace Modules\SMS\Services;

use Modules\SMS\Models\PhoneBookCategory;
use Modules\SMS\Repositories\PhoneBookCategoryRepository;

class PhoneBookCategoryService
{
    public function __construct(
        private readonly PhoneBookCategoryRepository $phoneBookCategoryRepository
    ) {}

    public function getPhoneBookCategories()
    {
        return $this->phoneBookCategoryRepository->all();
    }

    public function store(array $data): ?PhoneBookCategory
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->phoneBookCategoryRepository->create($data);
    }

    public function update(array $data, $id): ?bool
    {
        return $this->phoneBookCategoryRepository->update($data, $id);
    }

    public function findById($id): ?PhoneBookCategory
    {
        return $this->phoneBookCategoryRepository->show((int) $id);
    }

    public function delete($id): ?bool
    {
        return $this->phoneBookCategoryRepository->delete($id);
    }
}
