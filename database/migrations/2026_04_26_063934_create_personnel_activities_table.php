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
        Schema::create('personnel_activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('personnel_section_method_id')->constrained('personnel_section_methods')->cascadeOnDelete();
            $table->string('activity');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnel_activities');
    }
};
