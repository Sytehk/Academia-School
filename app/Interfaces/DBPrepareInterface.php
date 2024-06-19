<?php

namespace App\Interfaces;

interface DBPrepareInterface
{
    public function prepareForDB(array $data): array;
}
