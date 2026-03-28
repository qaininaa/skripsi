<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_section_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('s_no');
            $table->string('room_name');
            $table->string('class', 20);
            $table->string('room_number', 50)->nullable();
            $table->string('location_number', 50)->nullable();
            $table->unsignedSmallInteger('alert_limit_bacteria')->nullable();
            $table->unsignedSmallInteger('alert_limit_fungi')->nullable();
            $table->unsignedSmallInteger('action_limit_bacteria')->nullable();
            $table->unsignedSmallInteger('action_limit_fungi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_locations');
    }
};
