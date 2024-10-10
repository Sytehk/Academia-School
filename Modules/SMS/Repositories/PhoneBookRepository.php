<?php

declare(strict_types=1);

namespace Modules\SMS\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\SMS\Models\PhoneBook;

class PhoneBookRepository implements BaseRepositoryInterface
{
    public function all(array $filter = []): Collection
    {
        $query = PhoneBook::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->with('category', 'class', 'section');

        if (! empty($filter['class_id'])) {
            $query->where('class_id', $filter['class_id']);
        }

        if (! empty($filter['section_id'])) {
            $query->where('section_id', $filter['section_id']);
        }

        return $query->latest()->get();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = PhoneBook::query();

        return $query->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create($data): ?PhoneBook
    {
        $phoneBook = PhoneBook::create($data);

        return $phoneBook;
    }

    public function update(array $data, $id): ?bool
    {
        $phoneBook = PhoneBook::find($id);
        if ($phoneBook) {
            $phoneBook->update($data);

            return true;
        }

        return false;
    }

    public function show(int $id): ?PhoneBook
    {
        return PhoneBook::with('category')->find($id);
    }

    public function delete($id): ?bool
    {
        PhoneBook::destroy($id);

        return true;
    }
}
