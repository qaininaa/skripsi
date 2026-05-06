<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incubator_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_type_id')->constrained('report_types')->cascadeOnDelete();
            $table->string('temperature_label');
            $table->unsignedTinyInteger('min_day');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incubator_types');
    }
};
