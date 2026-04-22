<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('report_section_signatures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_id')->constrained('reports')->cascadeOnDelete();
            // section_id references the sections table (UUID), not the report_section pivot
            $table->foreignUuid('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['monitoring', 'reading', 'supervisor', 'manager']);
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();

            // Satu user hanya boleh punya satu TTD per seksi per role per laporan
            $table->unique(['report_id', 'section_id', 'user_id', 'role'], 'signature_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_section_signatures');
    }
};
