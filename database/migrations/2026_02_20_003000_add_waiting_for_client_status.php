<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to modify the ENUM column as Doctrine DBAL has issues with ENUMs sometimes
        DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('open', 'in_progress', 'resolved', 'closed', 'waiting_for_client') DEFAULT 'open'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original ENUM values
        DB::statement("UPDATE tickets SET status = 'open' WHERE status = 'waiting_for_client'");
        DB::statement("ALTER TABLE tickets MODIFY COLUMN status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open'");
    }
};
