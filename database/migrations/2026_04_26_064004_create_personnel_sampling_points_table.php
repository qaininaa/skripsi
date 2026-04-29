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
        Schema::create('personnel_sampling_points', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('personnel_section_method_id')->constrained('personnel_section_methods')->cascadeOnDelete();
            $table->string('sampling_point');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnel_sampling_points');
    }
};
