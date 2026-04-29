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
        Schema::create('personnel_rows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('personnel_instance_id')->constrained('personnel_instances')->cascadeOnDelete();
            $table->unsignedTinyInteger('row_order');
            $table->string('personnel_name')->nullable();
            $table->time('monitoring_time')->nullable();
            $table->string('class', 1)->nullable();
            $table->json('activities')->nullable();
            $table->foreignUuid('filled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnel_rows');
    }
};
