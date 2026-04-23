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
            // section_id references the sections table (UUID = template section dari report_type)
            $table->foreignUuid('section_id')->constrained('sections')->cascadeOnDelete();
            // instance_number untuk membedakan duplikasi section (1 = asli, 2+ = duplikat)
            $table->unsignedTinyInteger('instance_number')->default(1);
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['monitoring', 'reading', 'supervisor', 'manager']);
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();

            // Satu user hanya boleh punya satu TTD per seksi per instance per role per laporan
            $table->unique(['report_id', 'section_id', 'instance_number', 'user_id', 'role'], 'signature_unique');
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
