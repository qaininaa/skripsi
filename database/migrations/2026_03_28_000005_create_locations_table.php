<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('section_id')->nullable()->constrained('sections')->cascadeOnDelete();
            $table->foreignUuid('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->enum('frequency', ['operational', 'daily', 'weekly', 'monthly', 'semi_annual'])->nullable();
            $table->string('location_number', 50)->nullable();
            $table->string('measurement_type', 50)->nullable();
            $table->unsignedSmallInteger('alert_limit_total')->nullable();
            $table->unsignedSmallInteger('alert_limit_fungi')->nullable();
            $table->unsignedSmallInteger('alert_action_total')->nullable();
            $table->unsignedSmallInteger('alert_action_fungi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
