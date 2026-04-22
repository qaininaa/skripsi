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
        Schema::create('incubators', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_id')->constrained('reports')->cascadeOnDelete();
            $table->foreignUuid('report_type_incubator_id')->constrained('report_type_incubators')->cascadeOnDelete();
            $table->string('no_id')->nullable();
            $table->date('calibration_date')->nullable();
            $table->date('due_date_calibration')->nullable();
            $table->foreignUuid('incubated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date_in')->nullable();
            $table->string('time_in')->nullable();
            $table->foreignUuid('removed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date_out')->nullable();
            $table->string('time_out')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incubators');
    }
};
