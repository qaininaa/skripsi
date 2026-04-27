<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('env_section_instances')) {
            Schema::create('env_section_instances', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('report_section_id')->constrained('report_sections')->cascadeOnDelete();
                $table->foreignUuid('report_id')->constrained('reports')->cascadeOnDelete();
                $table->foreignUuid('parent_instance_id')->nullable()->constrained('env_section_instances')->cascadeOnDelete();
                $table->string('reason')->nullable();
                $table->timestamps();
            });

            return;
        }

        if (! Schema::hasColumn('env_section_instances', 'reason')) {
            Schema::table('env_section_instances', function (Blueprint $table) {
                $table->string('reason')->nullable()->after('parent_instance_id');
            });
        }

        if (! Schema::hasColumn('env_section_instances', 'created_at')) {
            Schema::table('env_section_instances', function (Blueprint $table) {
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasColumn('env_section_instances', 'updated_at')) {
            Schema::table('env_section_instances', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable();
            });
        }

        $this->ensureForeignKey('env_section_instances', 'report_section_id', 'report_sections');
        $this->ensureForeignKey('env_section_instances', 'report_id', 'reports');
        $this->ensureForeignKey('env_section_instances', 'parent_instance_id', 'env_section_instances');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('env_section_instances');
    }

    private function ensureForeignKey(string $tableName, string $column, string $referencesTable): void
    {
        $constraintName = "{$tableName}_{$column}_foreign";
        if ($this->foreignKeyExists($tableName, $constraintName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($column, $referencesTable, $constraintName) {
            $table->foreign($column, $constraintName)->references('id')->on($referencesTable)->cascadeOnDelete();
        });
    }

    private function foreignKeyExists(string $tableName, string $constraintName): bool
    {
        return DB::table('information_schema.table_constraints')
            ->where('constraint_schema', DB::getDatabaseName())
            ->where('table_name', $tableName)
            ->where('constraint_name', $constraintName)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();
    }
};
