<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_types', function (Blueprint $table) {
            // JSON map: { "medium_tsp_60": "Medium TSP 60mm", "medium_swab": "Swab Kit", ... }
            // null = laporan tidak menggunakan medium (tidak tampilkan seksi Identitas Medium)
            $table->json('medium_groups')->nullable()->after('frequency');
        });
    }

    public function down(): void
    {
        Schema::table('report_types', function (Blueprint $table) {
            $table->dropColumn('medium_groups');
        });
    }
};
