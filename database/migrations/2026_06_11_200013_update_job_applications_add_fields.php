<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add employer_notes and snapshot FK
        Schema::table('job_applications', function (Blueprint $table) {
            $table->text('employer_notes')->nullable()->after('cover_letter');
            $table->foreignId('status_snapshot_id')
                  ->nullable()
                  ->after('employer_notes')
                  ->constrained('application_status_history')
                  ->nullOnDelete();
        });

        // Extend the status enum — MySQL, PostgreSQL and SQLite differ
        if (DB::connection()->getDriverName() === 'pgsql') {
            // Step 1: Drop existing constraint
            DB::statement("ALTER TABLE job_applications DROP CONSTRAINT IF EXISTS job_applications_status_check");

            // Step 2: Widen column to VARCHAR so old values don't block the ALTER
            DB::statement("ALTER TABLE job_applications ALTER COLUMN status TYPE VARCHAR(20)");

            // Step 3: Migrate old 'reviewed' rows → 'viewed' (new canonical value)
            DB::table('job_applications')
                ->where('status', 'reviewed')
                ->update(['status' => 'viewed']);

            // Step 4: Add new constraint (includes 'reviewed' for safety in case
            //         other legacy data exists that hasn't been migrated yet)
            DB::statement("ALTER TABLE job_applications ADD CONSTRAINT job_applications_status_check CHECK (status IN ('pending','viewed','reviewed','shortlisted','accepted','rejected','cancelled'))");
        } elseif (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite does not support MODIFY COLUMN for ENUM/CHECK constraints
        } else {
            // MySQL: MODIFY COLUMN handles value migration automatically
            DB::statement("ALTER TABLE job_applications MODIFY COLUMN status ENUM('pending','viewed','shortlisted','accepted','rejected','cancelled') DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropForeign(['status_snapshot_id']);
            $table->dropColumn(['employer_notes', 'status_snapshot_id']);
        });
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE job_applications DROP CONSTRAINT IF EXISTS job_applications_status_check");
            DB::statement("ALTER TABLE job_applications ALTER COLUMN status TYPE VARCHAR(20)");
            DB::statement("ALTER TABLE job_applications ADD CONSTRAINT job_applications_status_check CHECK (status IN ('pending','reviewed','accepted','rejected'))");
        } elseif (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite does not support MODIFY COLUMN for ENUM/CHECK constraints
        } else {
            DB::statement("ALTER TABLE job_applications MODIFY COLUMN status ENUM('pending','reviewed','accepted','rejected') DEFAULT 'pending'");
        }
    }
};
