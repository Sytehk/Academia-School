<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemModule extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'version',
        'description',
        'enabled',
        'status',
        'dependencies',
        'minimum_app_version',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'dependencies' => 'array',
        ];
    }
}
