<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_environmental_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('env_section_instance_id')->constrained('env_section_instances')->cascadeOnDelete();
            $table->unsignedTinyInteger('period_number')->default(0);
            $table->unsignedTinyInteger('shift');
            $table->foreignUuid('analyst_id')->constrained('users')->cascadeOnDelete();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('cfu_bacteria', 20)->nullable();
            $table->string('cfu_fungi', 20)->nullable();
            $table->timestamps();

            $table->unique(['env_section_instance_id', 'period_number', 'shift'], 'entry_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_environmental_entries');
    }
};
