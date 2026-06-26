<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            // Guard each column with hasColumn to prevent duplicate column errors
            // (can happen if migration was partially run on this DB before)
            if (! Schema::hasColumn('job_listings', 'hours')) {
                $table->string('hours', 100)->nullable();
            }
            if (! Schema::hasColumn('job_listings', 'language')) {
                $table->string('language', 100)->nullable();
            }
            if (! Schema::hasColumn('job_listings', 'legal_status')) {
                $table->string('legal_status', 100)->nullable();
            }
            if (! Schema::hasColumn('job_listings', 'eligibility')) {
                // ->comment() removed: not supported on ALTER TABLE in PostgreSQL
                $table->string('eligibility', 100)->nullable()->default('Unknown');
            }
            if (! Schema::hasColumn('job_listings', 'verification_required')) {
                $table->boolean('verification_required')->default(true);
            }
            if (! Schema::hasColumn('job_listings', 'job_type_id')) {
                $table->foreignId('job_type_id')->nullable()
                      ->constrained('job_types')->nullOnDelete();
            }
        });

        // Extend risk_level — MySQL, PostgreSQL and SQLite differ
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE job_listings DROP CONSTRAINT IF EXISTS job_listings_risk_level_check");
            DB::statement("ALTER TABLE job_listings ALTER COLUMN risk_level TYPE VARCHAR(20)");
            DB::statement("ALTER TABLE job_listings ADD CONSTRAINT job_listings_risk_level_check CHECK (risk_level IN ('low','medium','high','critical'))");
        } elseif (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite does not support MODIFY COLUMN for ENUM/CHECK constraints
        } else {
            DB::statement("ALTER TABLE job_listings MODIFY COLUMN risk_level ENUM('low','medium','high','critical') DEFAULT 'low'");
        }
    }

    public function down(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropForeign(['job_type_id']);
            $table->dropColumn(['hours', 'language', 'legal_status', 'eligibility', 'verification_required', 'job_type_id']);
        });
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE job_listings DROP CONSTRAINT IF EXISTS job_listings_risk_level_check");
            DB::statement("ALTER TABLE job_listings ALTER COLUMN risk_level TYPE VARCHAR(20)");
            DB::statement("ALTER TABLE job_listings ADD CONSTRAINT job_listings_risk_level_check CHECK (risk_level IN ('low','medium','high'))");
        } elseif (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite does not support MODIFY COLUMN for ENUM/CHECK constraints
        } else {
            DB::statement("ALTER TABLE job_listings MODIFY COLUMN risk_level ENUM('low','medium','high') DEFAULT 'low'");
        }
    }
};
