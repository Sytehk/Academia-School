<?php

namespace App\Interfaces;

use Illuminate\Contracts\Pagination\Paginator;

interface CrudInterface
{
    public function getAll(array $filterData): Paginator;

    public function getCount(): int;

    public function getByColumn(string $columnName, $columnValue, array $selects = ['*']): ?object;

    public function getById(int $id): ?object;

    public function create(array $data);

    public function update(int $id, array $data): ?object;

    public function delete(int $id): ?object;
}
