<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('report_section_id')->constrained('report_section')->cascadeOnDelete();
            $table->unsignedTinyInteger('period_number')->default(1);
            $table->unsignedTinyInteger('shift');
            $table->foreignId('analyst_id')->constrained('users')->cascadeOnDelete();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->unsignedSmallInteger('cfu_bacteria')->nullable();
            $table->unsignedSmallInteger('cfu_fungi')->nullable();
            $table->timestamps();

            $table->unique(['report_id', 'report_section_id', 'period_number', 'shift'], 'entry_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_entries');
    }
};
