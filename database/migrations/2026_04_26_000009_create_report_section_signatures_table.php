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
        Schema::create('sectionSignatures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_id')->constrained('reports')->cascadeOnDelete();
            $table->foreignUuid('section_id')->constrained('sections')->cascadeOnDelete();
            $table->unsignedTinyInteger('instance_number')->default(1);
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['monitoring', 'reading', 'supervisor', 'manager']);
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();

            $table->unique(['report_id', 'section_id', 'instance_number', 'user_id', 'role'], 'signature_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sectionSignatures');
    }
};
