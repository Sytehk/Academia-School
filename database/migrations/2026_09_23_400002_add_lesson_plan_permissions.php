<?php

use App\Support\PermissionInstaller;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private array $permissions = [
        'lesson_plan_view',
        'lesson_plan_manage',
    ];

    public function up(): void
    {
        PermissionInstaller::install($this->permissions);
    }

    public function down(): void
    {
        PermissionInstaller::uninstall($this->permissions);
    }
};
