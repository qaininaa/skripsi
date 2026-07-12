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
        Schema::create('field_locks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('table_name');
            $table->uuid('row_id');
            $table->string('field_name');
            $table->foreignUuid('filled_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('filled_at')->nullable();
            $table->timestamps();

            $table->unique(['table_name', 'row_id', 'field_name']);
            $table->index(['table_name', 'row_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_locks');
    }
};
