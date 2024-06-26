<?php

declare(strict_types=1);

namespace Modules\Academic\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Academic\Models\Signature;

class SignatureRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return Signature::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->get();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = Signature::query();

        return $query->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create(array $data): ?Signature
    {
        return Signature::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return Signature::find($id)->update($data);
    }

    public function delete(int $id): int
    {
        return Signature::destroy($id);
    }

    public function show(int $id): ?Signature
    {
        return Signature::find($id);
    }
}
