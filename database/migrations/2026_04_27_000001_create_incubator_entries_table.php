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
        Schema::create('incubator_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('incubator_id')->constrained('incubators')->cascadeOnDelete();
            // 'monitoring' = medium monitoring (TSP plates), 'swab' = swab medium
            $table->string('medium_type');
            $table->foreignUuid('incubated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date_in')->nullable();
            $table->string('time_in')->nullable();
            $table->foreignUuid('removed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date_out')->nullable();
            $table->string('time_out')->nullable();
            $table->timestamps();

            $table->unique(['incubator_id', 'medium_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incubator_entries');
    }
};
