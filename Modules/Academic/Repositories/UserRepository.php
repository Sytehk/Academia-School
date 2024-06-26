<?php

declare(strict_types=1);

namespace Modules\Academic\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Authentication\Models\User;

class UserRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return User::all();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = User::query();

        return $query->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create(array $data): ?User
    {
        return User::create($data);
    }

    public function show(int $id): ?User
    {
        return User::find($id);
    }

    public function update(array $data, int $id): mixed
    {
        $user = $this->show((int) $id);

        return $user?->update($data) ?? false;
    }

    public function delete(int $id): void
    {
        // Find the user by ID
        $user = $this->show((int) $id);

        if (! $user) {
            return;
        }

        // Detach associated roles
        $user->roles()->detach();

        // Delete the user
        $user->delete();
    }
}
