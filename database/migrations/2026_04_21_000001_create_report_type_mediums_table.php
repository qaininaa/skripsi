<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_type_mediums', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_type_id')->constrained('report_types')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_type_mediums');
    }
};
