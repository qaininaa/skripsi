<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_type_id')->constrained('report_types')->cascadeOnDelete();
            $table->string('product_name');
            $table->string('batch_number');
            // $table->json('analyst_monitoring')->nullable();
            // $table->json('analyst_reading')->nullable();
            $table->string('status', 30)->default('pending');
            $table->json('header_data')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->index('batch_number');
            $table->index('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
