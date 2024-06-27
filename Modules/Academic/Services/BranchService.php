<?php

declare(strict_types=1);

namespace Modules\Academic\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\Academic\Repositories\BranchRepository;
use Modules\Authentication\Models\Branch;

class BranchService
{
    public function __construct(
        private readonly BranchRepository $branchRepository
    ) {}

    /**
     * Get categories by filtering args.
     */
    public function get(array $args = []): Collection|Builder
    {
        $orderBy = empty($args['order_by']) ? 'id' : $args['order_by']; // column name
        $order = empty($args['order']) ? 'asc' : $args['order']; // asc, desc
        $query = Branch::where('institute_id', get_institute_id())->orderBy($orderBy, $order);

        if (isset($args['is_query']) && $args['is_query']) {
            return $query;
        }

        return $query->get();
    }

    public function getBranches(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->branchRepository->paginate($perPage, $filter);
    }

    public function findBranchById(int $id): ?Branch
    {
        return $this->branchRepository->show((int) $id);
    }

    public function createBranch(array $data): ?Branch
    {
        $data['institute_id'] = get_institute_id();

        return $this->branchRepository->create($data);
    }

    public function updateBranch(array $data, int $id): mixed
    {
        return $this->branchRepository->update($data, $id);
    }

    public function deleteBranchById(int $id)
    {
        return $this->branchRepository->delete($id);
    }
}
