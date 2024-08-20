<?php

declare(strict_types=1);

namespace Modules\Finance\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Academic\Models\AbsentFine;

class AbsentFineRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return AbsentFine::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->get();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = AbsentFine::query();

        return $query->orderBy('id', 'asc')->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create(array $data): ?AbsentFine
    {
        return AbsentFine::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return AbsentFine::find($id)->update($data);
    }

    public function delete(int $id): int
    {
        return AbsentFine::destroy($id);
    }

    public function show(int $id): ?AbsentFine
    {
        return AbsentFine::with('class', 'period')->find($id);
    }
}
