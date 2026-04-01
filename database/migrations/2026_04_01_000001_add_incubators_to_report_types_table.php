<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_types', function (Blueprint $table) {
            $table->json('incubators')->nullable()->after('medium_groups');
        });
    }

    public function down(): void
    {
        Schema::table('report_types', function (Blueprint $table) {
            $table->dropColumn('incubators');
        });
    }
};
