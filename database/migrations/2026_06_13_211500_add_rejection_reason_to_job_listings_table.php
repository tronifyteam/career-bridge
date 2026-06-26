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
        Schema::table('job_listings', function (Blueprint $table) {
            if (!Schema::hasColumn('job_listings', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('status');
            }
        });

        // Extend status enum — MySQL, PostgreSQL and SQLite differ
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE job_listings DROP CONSTRAINT IF EXISTS job_listings_status_check");
            DB::statement("ALTER TABLE job_listings ALTER COLUMN status TYPE VARCHAR(30)");
            DB::statement("ALTER TABLE job_listings ADD CONSTRAINT job_listings_status_check CHECK (status IN ('draft', 'submitted_for_review', 'published', 'paused', 'closed', 'rejected'))");
        } elseif (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite doesn't enforce check constraints by default or allow simple column updates, ignoring
        } else {
            // MySQL
            DB::statement("ALTER TABLE job_listings MODIFY COLUMN status ENUM('draft', 'submitted_for_review', 'published', 'paused', 'closed', 'rejected') DEFAULT 'published'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            if (Schema::hasColumn('job_listings', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE job_listings DROP CONSTRAINT IF EXISTS job_listings_status_check");
            DB::statement("ALTER TABLE job_listings ALTER COLUMN status TYPE VARCHAR(30)");
            DB::statement("ALTER TABLE job_listings ADD CONSTRAINT job_listings_status_check CHECK (status IN ('draft', 'submitted_for_review', 'published', 'paused', 'closed'))");
        } elseif (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite
        } else {
            DB::statement("ALTER TABLE job_listings MODIFY COLUMN status ENUM('draft', 'submitted_for_review', 'published', 'paused', 'closed') DEFAULT 'published'");
        }
    }
};
