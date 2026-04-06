<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_section', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_section')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('id_location')->constrained('locations')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['id_section', 'id_location']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_section');
    }
};
