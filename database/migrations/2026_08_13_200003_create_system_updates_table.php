<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_updates', function (Blueprint $table) {
            $table->id();
            $table->string('from_version')->nullable();
            $table->string('to_version')->nullable();
            $table->string('type')->default('manual');
            $table->string('status')->default('pending');
            $table->string('package_name')->nullable();
            $table->unsignedBigInteger('package_size')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('backup_path')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('executed_by')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_updates');
    }
};
