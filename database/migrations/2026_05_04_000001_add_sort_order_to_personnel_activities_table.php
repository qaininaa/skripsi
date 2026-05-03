<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('personnel_activities', 'sort_order')) {
            Schema::table('personnel_activities', function (Blueprint $table) {
                $table->unsignedSmallInteger('sort_order')->default(0)->after('activity');
            });
        }

        $methodIds = DB::table('personnel_activities')
            ->select('personnel_section_method_id')
            ->distinct()
            ->pluck('personnel_section_method_id');

        foreach ($methodIds as $methodId) {
            $activityIds = DB::table('personnel_activities')
                ->where('personnel_section_method_id', $methodId)
                ->orderBy('created_at')
                ->orderBy('id')
                ->pluck('id');

            foreach ($activityIds as $index => $activityId) {
                DB::table('personnel_activities')
                    ->where('id', $activityId)
                    ->update(['sort_order' => $index + 1]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('personnel_activities', 'sort_order')) {
            Schema::table('personnel_activities', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
