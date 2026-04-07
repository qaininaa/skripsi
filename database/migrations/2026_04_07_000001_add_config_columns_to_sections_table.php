<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            // Label prefix for columns: "Exposure", "Shift", or any custom text
            $table->string('column_label', 50)->default('Exposure')->after('max_exposure');

            // Time input configuration per column
            // none        = no time inputs
            // single      = one start/end pair per column (e.g. air_sampler exposure)
            // dual_ab     = two time slots A/B per column (e.g. settle plate)
            // swab        = three time slots S1, S1-2, S1-3 per column
            $table->string('time_slot_type', 20)->default('none')->after('column_label');

            // Whether there's a shared "Machine Set-up" time row before the main columns
            $table->boolean('has_shared_time')->default(false)->after('time_slot_type');

            // Whether shift assignment toggle (S1/S2) is shown per column
            $table->boolean('has_shift_toggle')->default(true)->after('has_shared_time');
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropColumn(['column_label', 'time_slot_type', 'has_shared_time', 'has_shift_toggle']);
        });
    }
};
