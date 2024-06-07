<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Institute-wise admission number generation.
 *
 * `institutes.admission_no_mode` (manual | auto) and `admission_no_prefix`
 * are the per-institute settings; `admission_number_sequences` tracks the
 * next number to hand out, one row per institute, locked with
 * `lockForUpdate()` at generation time so two concurrent student creations
 * can never be handed the same number.
 *
 * Existing students keep whatever registration_no they already have —
 * nothing here touches existing rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutes', function (Blueprint $table) {
            $table->string('admission_no_mode', 10)->default('manual')->after('name');
            $table->string('admission_no_prefix', 20)->nullable()->after('admission_no_mode');
        });

        Schema::create('admission_number_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->unique()->constrained('institutes')->cascadeOnDelete();
            $table->unsignedInteger('next_number')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_number_sequences');

        Schema::table('institutes', function (Blueprint $table) {
            $table->dropColumn(['admission_no_mode', 'admission_no_prefix']);
        });
    }
};
