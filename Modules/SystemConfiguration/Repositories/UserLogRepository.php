<?php

declare(strict_types=1);

namespace Modules\SystemConfiguration\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\SystemConfiguration\Models\UserLog;

class UserLogRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return UserLog::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->get();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = UserLog::query();

        return $query->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->with('user')->paginate($perPage);
    }

    public function create(array $data): ?UserLog
    {
        return UserLog::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return UserLog::find($id)->update($data);
    }

    public function delete(int $id): int
    {
        return UserLog::destroy($id);
    }

    public function show(int $id): ?UserLog
    {
        return UserLog::find($id);
    }
}
