<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_personnel', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_environmental_entry_id')
                ->constrained('report_environmental_entries')
                ->cascadeOnDelete();
            $table->string('nama_personal');
            $table->time('jam_pemantauan')->nullable();
            $table->json('aktivitas')->nullable(); // array: "Keluar ruang filling", "Akhir proses analisa"
            $table->string('titik_sampling')->nullable();
            $table->string('kelas')->nullable(); // B, C, B/C
            $table->unsignedTinyInteger('hasil_pengamatan_b')->nullable();
            $table->unsignedTinyInteger('hasil_pengamatan_f')->nullable();
            $table->unsignedTinyInteger('hasil_pengamatan_t')->nullable();
            $table->enum('kesimpulan', ['MS', 'TMS'])->nullable();
            $table->timestamps();

            $table->index('report_environmental_entry_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_personnel');
    }
};
