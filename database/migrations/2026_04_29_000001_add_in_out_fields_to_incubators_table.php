<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Intentionally no-op.
        // In/out tracking now lives in incubator_entries.
    }

    public function down(): void
    {
        // Intentionally no-op.
    }
};
