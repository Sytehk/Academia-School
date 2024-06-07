<?php

use App\Support\PermissionInstaller;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private array $permissions = [
        'layouts_certificates_bulk_issue',
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
