<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemoRestriction extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'type',
        'enabled',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }
}
