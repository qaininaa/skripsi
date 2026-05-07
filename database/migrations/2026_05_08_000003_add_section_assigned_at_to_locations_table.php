<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->timestamp('section_assigned_at')->nullable()->after('section_id');
            $table->index('section_assigned_at');
        });

        DB::table('locations')
            ->whereNotNull('section_id')
            ->whereNull('section_assigned_at')
            ->update([
                'section_assigned_at' => DB::raw('COALESCE(updated_at, created_at)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropIndex(['section_assigned_at']);
            $table->dropColumn('section_assigned_at');
        });
    }
};
