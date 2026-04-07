<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_type_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('measurement_unit', 50)->default('cfu');
            $table->string('measurement_type', 50)->default('exposure');
            $table->unsignedTinyInteger('max_exposure')->default(1);
            $table->string('column_label', 50)->default('Exposure');
            $table->string('time_slot_type', 20)->default('none');
            $table->boolean('has_shared_time')->default(false);
            $table->boolean('has_shift_toggle')->default(true);
            $table->unsignedTinyInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_sections');
    }
};
