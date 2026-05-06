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
        Schema::create('personnel_method_limits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('personnel_section_method_id')->constrained('personnel_section_methods')->cascadeOnDelete();
            $table->enum('class', ['b', 'c']);
            $table->enum('limit_type', ['alert', 'action']);
            $table->unsignedSmallInteger('cfu_total');
            $table->unsignedSmallInteger('cfu_fungi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnel_method_limits');
    }
};
