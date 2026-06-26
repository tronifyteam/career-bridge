<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * M5 — Add auto-screening fields to job_listings table.
 *
 * - red_flags:       JSON array of rule-based red flag strings
 * - missing_fields:  JSON array of missing required field names
 * - screened_at:     When the auto-screening was last run
 *
 * Note: risk_level column already exists from a previous migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            if (!Schema::hasColumn('job_listings', 'red_flags')) {
                $table->json('red_flags')->nullable()->after('risk_level');
            }
            if (!Schema::hasColumn('job_listings', 'missing_fields')) {
                $table->json('missing_fields')->nullable()->after('red_flags');
            }
            if (!Schema::hasColumn('job_listings', 'screened_at')) {
                $table->timestamp('screened_at')->nullable()->after('missing_fields');
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropColumnIfExists('red_flags');
            $table->dropColumnIfExists('missing_fields');
            $table->dropColumnIfExists('screened_at');
        });
    }
};
