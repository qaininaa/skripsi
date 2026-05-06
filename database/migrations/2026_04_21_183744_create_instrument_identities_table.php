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
        Schema::create('instrument_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_id')->constrained('reports')->cascadeOnDelete();
            $table->string('tool_name')->default('Air Sampler');
            $table->string('no_id')->nullable();
            $table->date('calibration_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();

            // One record per tool type per report
            $table->unique(['report_id', 'tool_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instrument_entries');
    }
};
