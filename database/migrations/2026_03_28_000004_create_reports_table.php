<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_type_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('nama_produk');
            $table->string('nomor_batch_produk');
            $table->foreignId('shift1_analis_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('shift2_analis_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 30)->default('pending');
            // pending → in_progress → submitted → approved / rejected
            $table->json('header_data')->nullable();
            // Berisi: identitas instrumen + identitas medium (diisi analis)
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['nomor_batch_produk']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
