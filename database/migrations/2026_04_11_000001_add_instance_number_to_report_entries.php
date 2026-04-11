<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('report_entries', 'instance_number')) {
                $table->unsignedTinyInteger('instance_number')->default(1)->after('report_section_id');
            }
            // Add a plain index on report_id so MySQL can use it for the FK
            // while we swap the unique constraint
            if (! \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM report_entries WHERE Key_name = 'report_entries_report_id_tmp'")) {
                $table->index('report_id', 'report_entries_report_id_tmp');
            }
        });

        Schema::table('report_entries', function (Blueprint $table) {
            // Drop old unique (may already be gone from a partial run)
            $indexes = \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM report_entries WHERE Key_name = 'entry_unique'");
            if ($indexes) {
                $table->dropUnique('entry_unique');
            }
            // Add new unique if not already using 5 columns
            $newCols = \Illuminate\Support\Facades\DB::select(
                "SHOW INDEX FROM report_entries WHERE Key_name = 'entry_unique' AND Column_name = 'instance_number'"
            );
            if (! $newCols) {
                $table->unique(
                    ['report_id', 'report_section_id', 'instance_number', 'period_number', 'shift'],
                    'entry_unique'
                );
            }
            // Remove the temporary plain index
            $tmp = \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM report_entries WHERE Key_name = 'report_entries_report_id_tmp'");
            if ($tmp) {
                $table->dropIndex('report_entries_report_id_tmp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('report_entries', function (Blueprint $table) {
            if (! \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM report_entries WHERE Key_name = 'report_entries_report_id_tmp'")) {
                $table->index('report_id', 'report_entries_report_id_tmp');
            }
        });

        Schema::table('report_entries', function (Blueprint $table) {
            $indexes = \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM report_entries WHERE Key_name = 'entry_unique'");
            if ($indexes) {
                $table->dropUnique('entry_unique');
            }
            if (Schema::hasColumn('report_entries', 'instance_number')) {
                $table->dropColumn('instance_number');
            }
            $newCols = \Illuminate\Support\Facades\DB::select(
                "SHOW INDEX FROM report_entries WHERE Key_name = 'entry_unique'"
            );
            if (! $newCols) {
                $table->unique(
                    ['report_id', 'report_section_id', 'period_number', 'shift'],
                    'entry_unique'
                );
            }
            $tmp = \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM report_entries WHERE Key_name = 'report_entries_report_id_tmp'");
            if ($tmp) {
                $table->dropIndex('report_entries_report_id_tmp');
            }
        });
    }
};
