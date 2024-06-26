<?php

declare(strict_types=1);

namespace Modules\Academic\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Authentication\Models\Institute;

class InstituteRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return Institute::all();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = Institute::query();

        return $query->paginate($perPage);
    }

    public function create(array $data): ?Institute
    {
        return Institute::create($data);
    }

    public function show(int $id): ?Institute
    {
        return Institute::find($id);
    }

    public function update(array $data, int $id): mixed
    {
        return Institute::find((int) $id)->update($data);
    }

    public function delete(int $id): void
    {
        // Find the by ID
        $institute = $this->show((int) $id);

        // Delete the
        $institute->delete();
    }
}
