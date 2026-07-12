<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_section_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_id')->constrained('reports')->cascadeOnDelete();
            $table->foreignUuid('section_id')->constrained('sections')->cascadeOnDelete();
            $table->unsignedTinyInteger('instance_number')->default(1);
            $table->text('notes')->nullable();
            $table->string('conclusion', 10)->nullable();
            $table->timestamps();

            $table->unique(['report_id', 'section_id', 'instance_number'], 'report_section_notes_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_section_notes');
    }
};
