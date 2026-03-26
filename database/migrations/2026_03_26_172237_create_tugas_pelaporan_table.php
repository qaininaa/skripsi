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
        Schema::create('tugas_pelaporan', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('shift1_analis_id')->constrained('users');
            $table->foreignId('shift2_analis_id')->constrained('users');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tugas_pelaporan');
    }
};
