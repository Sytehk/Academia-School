<?php

namespace App\Interfaces;

interface DropdownInterface
{
    public function setTableName(string $tableName): self;

    public function setPrimaryColumn(string $primaryColumn): self;

    public function setSelectedColumns(array $selectedColumns): self;

    public function getDropdowns(): array;
}
