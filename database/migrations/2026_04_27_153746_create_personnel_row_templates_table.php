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
        Schema::create('personnel_row_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('personnel_section_method_id')->constrained('personnel_section_methods')->cascadeOnDelete();
            $table->foreignUuid('filled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnel_row_templates');
    }
};
