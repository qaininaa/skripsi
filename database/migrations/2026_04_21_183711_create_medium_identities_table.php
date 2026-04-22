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
        Schema::create('medium_identities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_id')->constrained('reports')->cascadeOnDelete();
            $table->string('name');                    // medium key, e.g. 'pha', 'tsa'
            $table->string('batch_number')->nullable();
            $table->string('gpt_number')->nullable();
            $table->date('expiration_date')->nullable();
            $table->timestamps();

            // One record per medium type per report
            $table->unique(['report_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medium_identities');
    }
};
