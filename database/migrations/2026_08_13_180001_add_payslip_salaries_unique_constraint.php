<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payslip_salaries', function (Blueprint $table) {
            $table->unique(
                ['institute_id', 'branch_id', 'user_id', 'year', 'month'],
                'payslip_salaries_tenant_user_period_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('payslip_salaries', function (Blueprint $table) {
            $table->dropUnique('payslip_salaries_tenant_user_period_unique');
        });
    }
};
