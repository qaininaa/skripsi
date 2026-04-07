<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('report_entries', function (Blueprint $table) {
            $table->decimal('cfu_bacteria', 8, 2)->nullable()->change();
            $table->decimal('cfu_fungi', 8, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_entries', function (Blueprint $table) {
            $table->unsignedSmallInteger('cfu_bacteria')->nullable()->change();
            $table->unsignedSmallInteger('cfu_fungi')->nullable()->change();
        });
    }
};
