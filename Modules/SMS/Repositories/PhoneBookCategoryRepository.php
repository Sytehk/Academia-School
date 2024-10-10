<?php

declare(strict_types=1);

namespace Modules\SMS\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\SMS\Models\PhoneBookCategory;

class PhoneBookCategoryRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return PhoneBookCategory::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->latest()->get();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = PhoneBookCategory::query();

        return $query->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create($data): ?PhoneBookCategory
    {
        $phoneBookCategory = PhoneBookCategory::create($data);

        return $phoneBookCategory;
    }

    public function update(array $data, $id): ?bool
    {
        $phoneBookCategory = PhoneBookCategory::find($id);
        if ($phoneBookCategory) {
            $phoneBookCategory->update($data);

            return true;
        }

        return false;
    }

    public function show(int $id): ?PhoneBookCategory
    {
        return PhoneBookCategory::find($id);
    }

    public function delete($id): ?bool
    {
        PhoneBookCategory::destroy($id);

        return true;
    }
}
