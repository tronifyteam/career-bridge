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
        // Extend status enum to include 'suspended'
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE job_listings DROP CONSTRAINT IF EXISTS job_listings_status_check");
            DB::statement("ALTER TABLE job_listings ALTER COLUMN status TYPE VARCHAR(30)");
            DB::statement("ALTER TABLE job_listings ADD CONSTRAINT job_listings_status_check CHECK (status IN ('draft', 'submitted_for_review', 'published', 'paused', 'closed', 'rejected', 'suspended'))");
        } elseif (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite doesn't enforce check constraints by default
        } else {
            // MySQL
            DB::statement("ALTER TABLE job_listings MODIFY COLUMN status ENUM('draft', 'submitted_for_review', 'published', 'paused', 'closed', 'rejected', 'suspended') DEFAULT 'published'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE job_listings DROP CONSTRAINT IF EXISTS job_listings_status_check");
            DB::statement("ALTER TABLE job_listings ALTER COLUMN status TYPE VARCHAR(30)");
            DB::statement("ALTER TABLE job_listings ADD CONSTRAINT job_listings_status_check CHECK (status IN ('draft', 'submitted_for_review', 'published', 'paused', 'closed', 'rejected'))");
        } elseif (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite
        } else {
            DB::statement("ALTER TABLE job_listings MODIFY COLUMN status ENUM('draft', 'submitted_for_review', 'published', 'paused', 'closed', 'rejected') DEFAULT 'published'");
        }
    }
};
