<?php

namespace App\Entity;

use App\Interfaces\DropdownInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class Dropdown implements DropdownInterface
{
    protected string $tableName;

    protected string $primaryColumn;

    protected array $selectedColumns = [];

    protected array $orderByColumnWithOrders = [];

    private Builder $query;

    public function setTableName(string $tableName): self
    {
        $this->tableName = $tableName;

        return $this;
    }

    public function setPrimaryColumn(string $primaryColumn): self
    {
        $this->primaryColumn = $primaryColumn;

        return $this;
    }

    public function setSelectedColumns(array $selectedColumns): self
    {
        $this->selectedColumns = $selectedColumns;

        return $this;
    }

    public function setOrderByColumnWithOrders(array $orderByColumnWithOrders): self
    {
        $this->orderByColumnWithOrders = $orderByColumnWithOrders;

        return $this;
    }

    public function getQuery(): Builder
    {
        return $this->query;
    }

    public function setQuery(Builder $query): self
    {
        $this->query = $query;

        return $this;
    }

    public function setSelectableTableQuery(): self
    {
        $this->query = DB::table($this->tableName)
            ->select($this->selectedColumns);

        return $this;
    }

    public function getDropdowns(): array
    {
        if (count($this->orderByColumnWithOrders)) {
            foreach ($this->orderByColumnWithOrders as $column => $columnOrder) {
                $this->query->orderBy($column, $columnOrder);
            }
        }

        return $this->query->get()->toArray();
    }
}
