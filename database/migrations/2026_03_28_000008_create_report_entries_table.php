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
            $table->foreignUuid('report_section_id')->constrained('report_section')->cascadeOnDelete();
            $table->unsignedTinyInteger('instance_number')->default(1);
            $table->unsignedTinyInteger('period_number')->default(1);
            $table->unsignedTinyInteger('shift');
            $table->foreignUuid('analyst_id')->constrained('users')->cascadeOnDelete();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('cfu_bacteria', 20)->nullable();
            $table->string('cfu_fungi', 20)->nullable();
            $table->timestamps();

            $table->unique(['report_id', 'report_section_id', 'instance_number', 'period_number', 'shift'], 'entry_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_environmental_entries');
    }
};
