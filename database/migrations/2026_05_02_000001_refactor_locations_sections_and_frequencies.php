<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addNewColumns();
        $this->migrateLocationFrequency();
        $this->migrateLocationSection();
        $this->migrateEnvSectionInstances();
        $this->dropLegacyConstraints();
        $this->dropLegacyTables();
    }

    public function down(): void
    {
        // Irreversible data migration.
    }

    private function addNewColumns(): void
    {
        if (Schema::hasTable('locations') && ! Schema::hasColumn('locations', 'section_id')) {
            Schema::table('locations', function (Blueprint $table) {
                $table->foreignUuid('section_id')->nullable()->after('id')->constrained('sections')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('locations') && ! Schema::hasColumn('locations', 'frequency')) {
            Schema::table('locations', function (Blueprint $table) {
                $table->string('frequency', 20)->nullable()->after('room_id');
            });
        }

        if (Schema::hasTable('env_section_instances') && ! Schema::hasColumn('env_section_instances', 'location_id')) {
            Schema::table('env_section_instances', function (Blueprint $table) {
                $table->foreignUuid('location_id')->nullable()->after('id')->constrained('locations')->cascadeOnDelete();
            });
        }
    }

    private function migrateLocationFrequency(): void
    {
        if (! Schema::hasTable('locations') || ! Schema::hasColumn('locations', 'frequency_id') || ! Schema::hasTable('frequencies')) {
            return;
        }

        $frequencyMap = DB::table('frequencies')->pluck('name', 'id');

        DB::table('locations')
            ->select('id', 'frequency_id')
            ->orderBy('id')
            ->get()
            ->each(function ($location) use ($frequencyMap): void {
                $frequency = $frequencyMap[$location->frequency_id] ?? null;

                if ($frequency === null) {
                    return;
                }

                DB::table('locations')
                    ->where('id', $location->id)
                    ->update(['frequency' => $frequency]);
            });
    }

    private function migrateLocationSection(): void
    {
        if (! Schema::hasTable('locations') || ! Schema::hasColumn('locations', 'section_id') || ! Schema::hasTable('report_sections')) {
            return;
        }

        DB::table('report_sections')
            ->select('location_id', 'section_id')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->groupBy('location_id')
            ->each(function ($rows, $locationId): void {
                $sectionId = $rows->first()->section_id;

                DB::table('locations')
                    ->where('id', $locationId)
                    ->whereNull('section_id')
                    ->update(['section_id' => $sectionId]);
            });
    }

    private function migrateEnvSectionInstances(): void
    {
        if (! Schema::hasTable('env_section_instances') || ! Schema::hasColumn('env_section_instances', 'report_section_id') || ! Schema::hasTable('report_sections')) {
            return;
        }

        $locationByPivot = DB::table('report_sections')->pluck('location_id', 'id');

        DB::table('env_section_instances')
            ->select('id', 'report_section_id')
            ->orderBy('id')
            ->get()
            ->each(function ($instance) use ($locationByPivot): void {
                $locationId = $locationByPivot[$instance->report_section_id] ?? null;

                if ($locationId === null) {
                    return;
                }

                DB::table('env_section_instances')
                    ->where('id', $instance->id)
                    ->update(['location_id' => $locationId]);
            });
    }

    private function dropLegacyConstraints(): void
    {
        if (Schema::hasTable('env_section_instances') && Schema::hasColumn('env_section_instances', 'report_section_id')) {
            Schema::table('env_section_instances', function (Blueprint $table) {
                $table->dropConstrainedForeignId('report_section_id');
            });
        }

        if (Schema::hasTable('locations') && Schema::hasColumn('locations', 'frequency_id')) {
            Schema::table('locations', function (Blueprint $table) {
                $table->dropConstrainedForeignId('frequency_id');
            });
        }
    }

    private function dropLegacyTables(): void
    {
        Schema::dropIfExists('report_sections');
        Schema::dropIfExists('frequencies');
    }
};