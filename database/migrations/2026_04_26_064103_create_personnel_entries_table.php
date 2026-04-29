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
        Schema::create('personnel_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('method_id')->constrained('personnel_section_methods')->cascadeOnDelete();
            $table->foreignUuid('personnel_instance_id')->constrained('personnel_instances')->cascadeOnDelete();
            $table->string('personnel_name')->nullable();
            $table->string('personnel_time')->nullable();
            $table->string('class')->nullable();
            $table->json('activities')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnel_entries');
    }
};
