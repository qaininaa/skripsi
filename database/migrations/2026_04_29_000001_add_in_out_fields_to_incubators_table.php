<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incubators', function (Blueprint $table) {
            $table->string('incubated_by')->nullable()->after('due_date_calibration');
            $table->date('date_in')->nullable()->after('incubated_by');
            $table->time('time_in')->nullable()->after('date_in');
            $table->string('removed_by')->nullable()->after('time_in');
            $table->date('date_out')->nullable()->after('removed_by');
            $table->time('time_out')->nullable()->after('date_out');
        });
    }

    public function down(): void
    {
        Schema::table('incubators', function (Blueprint $table) {
            $table->dropColumn(['incubated_by', 'date_in', 'time_in', 'removed_by', 'date_out', 'time_out']);
        });
    }
};
