<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: add temporary string columns
        Schema::table('report_entries', function (Blueprint $table) {
            $table->string('cfu_bacteria_str', 20)->nullable()->after('end_time');
            $table->string('cfu_fungi_str', 20)->nullable()->after('cfu_bacteria_str');
        });

        // Step 2: migrate data — normalize "3.00" → "3", keep null as null
        DB::statement("
            UPDATE report_entries
            SET cfu_bacteria_str = CASE
                WHEN cfu_bacteria IS NULL THEN NULL
                WHEN cfu_bacteria = FLOOR(cfu_bacteria) THEN CAST(CAST(cfu_bacteria AS SIGNED) AS CHAR)
                ELSE CAST(cfu_bacteria AS CHAR)
            END
        ");
        DB::statement("
            UPDATE report_entries
            SET cfu_fungi_str = CASE
                WHEN cfu_fungi IS NULL THEN NULL
                WHEN cfu_fungi = FLOOR(cfu_fungi) THEN CAST(CAST(cfu_fungi AS SIGNED) AS CHAR)
                ELSE CAST(cfu_fungi AS CHAR)
            END
        ");

        // Step 3: drop old decimal columns
        Schema::table('report_entries', function (Blueprint $table) {
            $table->dropColumn(['cfu_bacteria', 'cfu_fungi']);
        });

        // Step 4: rename temp columns to original names
        Schema::table('report_entries', function (Blueprint $table) {
            $table->renameColumn('cfu_bacteria_str', 'cfu_bacteria');
            $table->renameColumn('cfu_fungi_str', 'cfu_fungi');
        });
    }

    public function down(): void
    {
        Schema::table('report_entries', function (Blueprint $table) {
            $table->string('cfu_bacteria_str', 20)->nullable()->after('end_time');
            $table->string('cfu_fungi_str', 20)->nullable()->after('cfu_bacteria_str');
        });

        DB::statement("
            UPDATE report_entries
            SET cfu_bacteria_str = CASE
                WHEN cfu_bacteria IS NULL THEN NULL
                WHEN cfu_bacteria REGEXP '^[0-9]+$' THEN CAST(cfu_bacteria AS DECIMAL(8,2))
                ELSE NULL
            END
        ");
        DB::statement("
            UPDATE report_entries
            SET cfu_fungi_str = CASE
                WHEN cfu_fungi IS NULL THEN NULL
                WHEN cfu_fungi REGEXP '^[0-9]+$' THEN CAST(cfu_fungi AS DECIMAL(8,2))
                ELSE NULL
            END
        ");

        Schema::table('report_entries', function (Blueprint $table) {
            $table->dropColumn(['cfu_bacteria', 'cfu_fungi']);
        });

        Schema::table('report_entries', function (Blueprint $table) {
            $table->renameColumn('cfu_bacteria_str', 'cfu_bacteria');
            $table->renameColumn('cfu_fungi_str', 'cfu_fungi');
        });
    }
};
