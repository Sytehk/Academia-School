<?php

use App\Support\PermissionInstaller;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private array $permissions = [
        'exam_module_grade_online_answers',
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
