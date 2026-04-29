<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnel_instances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_id')->constrained('reports')->cascadeOnDelete();
            $table->foreignUuid('personnel_section_method_id')->constrained('personnel_section_methods')->cascadeOnDelete();
            $table->unsignedTinyInteger('page_number')->default(1);
            $table->text('note')->nullable();
            $table->text('deviation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnel_instances');
    }
};