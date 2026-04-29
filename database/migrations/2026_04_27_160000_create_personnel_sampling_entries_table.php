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
        Schema::create('personnel_sampling_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('personnel_row_id')->constrained('personnel_rows')->cascadeOnDelete();
            $table->foreignUuid('sampling_point_id')->constrained('personnel_sampling_points')->cascadeOnDelete();
            $table->string('cfu_bacteria')->nullable();
            $table->string('cfu_fungi')->nullable();
            $table->string('cfu_total')->nullable();
            $table->string('kesimpulan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnel_sampling_entries');
    }
};
