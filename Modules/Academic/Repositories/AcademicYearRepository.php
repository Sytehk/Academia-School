<?php

declare(strict_types=1);

namespace Modules\Academic\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Academic\Models\AcademicYear;

class AcademicYearRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return AcademicYear::orderBy('id', 'asc')->get();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = AcademicYear::query();

        // Dynamically apply filters
        if (! empty($filter['session'])) {
            $query->where('session', 'like', '%'.$filter['session'].'%');
        }
        if (! empty($filter['year'])) {
            $query->where('year', 'like', '%'.$filter['year'].'%');
        }

        // Pagination with order
        return $query->orderBy('id', 'asc')->select('id', 'session', 'year')->paginate($perPage);
    }

    public function create(array $data): ?AcademicYear
    {
        return AcademicYear::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return AcademicYear::find($id)->update($data);
    }

    public function delete(int $id): int
    {
        return AcademicYear::destroy($id);
    }

    public function show(int $id): ?AcademicYear
    {
        return AcademicYear::find($id);
    }
}
