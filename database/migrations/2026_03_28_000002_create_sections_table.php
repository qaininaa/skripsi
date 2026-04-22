<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_type_id')->constrained('report_types')->cascadeOnDelete();
            $table->string('name');
            $table->string('measurement_unit', 50);
            $table->string('measurement_type', 50);
            $table->unsignedTinyInteger('max_column')->default(1);
            $table->string('column_label', 50)->default('Exposure');
            $table->string('time_slot_type', 20)->default('none');
            $table->boolean('has_machine_setup')->default(false); // awalanya has_shared_time
            $table->unsignedTinyInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
