<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_sessions', function (Blueprint $table) {
            $table->index(
                ['institute_id', 'branch_id', 'session_id', 'class_id', 'section_id'],
                'student_sessions_context_lookup_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('student_sessions', function (Blueprint $table) {
            $table->dropIndex('student_sessions_context_lookup_idx');
        });
    }
};
