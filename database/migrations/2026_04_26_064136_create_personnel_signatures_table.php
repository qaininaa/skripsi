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
        Schema::create('personnel_signatures', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->foreignUuid('report_id')->constrained('reports')->cascadeOnDelete();
        $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
        $table->string('role'); // 'monitoring' / 'reading'
        $table->timestamp('signed_at')->nullable();
        $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnel_signatures');
    }
};
