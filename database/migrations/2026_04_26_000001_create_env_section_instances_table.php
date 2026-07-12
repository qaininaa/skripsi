<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('env_section_instances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignUuid('report_id')->constrained('reports')->cascadeOnDelete();
            $table->foreignUuid('parent_instance_id')->nullable()->constrained('env_section_instances')->cascadeOnDelete();
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('env_section_instances');
    }
};
