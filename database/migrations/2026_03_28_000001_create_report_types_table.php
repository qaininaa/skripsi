<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('annex_number', 50);
            $table->string('instrument', 50);          // air_sampler, settle_plate, contact_plate, swab
            $table->string('frequency', 50)->nullable(); // daily, weekly, etc.
            $table->json('medium_groups')->nullable();     // null = tidak ada seksi medium; map key→label
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_types');
    }
};
