<?php

use App\Support\PermissionInstaller;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private array $permissions = [
        'academic_transcript_issue',
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
