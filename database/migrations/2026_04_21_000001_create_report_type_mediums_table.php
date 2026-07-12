<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medium_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_type_id')->constrained('report_types')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medium_types');
    }
};
