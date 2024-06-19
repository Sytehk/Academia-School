<?php

declare(strict_types=1);

namespace App\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface BaseRepositoryInterface
{
    public function all(): Collection;

    /**
     * @throws \InvalidArgumentException
     */
    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator;

    public function create(array $data);

    public function update(array $data, int $id);

    public function delete(int $id);

    public function show(int $id);
}
