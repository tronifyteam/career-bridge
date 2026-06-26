<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'onboarding_step')) {
                $table->unsignedTinyInteger('onboarding_step')->default(1);
            }
            if (! Schema::hasColumn('users', 'selfie_file_url')) {
                $table->string('selfie_file_url', 500)->nullable();
            }
            if (! Schema::hasColumn('users', 'selfie_verified_at')) {
                $table->timestamp('selfie_verified_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'verified_badge_status')) {
                $table->string('verified_badge_status', 50)->default('unverified');
            }
            if (! Schema::hasColumn('users', 'verified_badge_updated_at')) {
                $table->timestamp('verified_badge_updated_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'ready_to_work_status')) {
                $table->string('ready_to_work_status', 50)->default('not_ready');
            }
            if (! Schema::hasColumn('users', 'ready_to_work_updated_at')) {
                $table->timestamp('ready_to_work_updated_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'sponsorship_required')) {
                $table->boolean('sponsorship_required')->default(false);
            }
            if (! Schema::hasColumn('users', 'employer_self_check_required')) {
                $table->boolean('employer_self_check_required')->default(false);
            }
            if (! Schema::hasColumn('users', 'available_date')) {
                $table->date('available_date')->nullable();
            }
            if (! Schema::hasColumn('users', 'expected_salary')) {
                $table->decimal('expected_salary', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('users', 'worker_type_id')) {
                $table->foreignId('worker_type_id')->nullable()
                      ->constrained('worker_types')->nullOnDelete();
            }
            if (! Schema::hasColumn('users', 'unified_business_number')) {
                $table->string('unified_business_number', 50)->nullable();
            }

            // Indexes — safe to add even if columns existed (IF NOT EXISTS handled by DB)
            // Wrap in try/catch in case indexes already exist
        });

        // Add indexes separately to handle "already exists" gracefully
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->index(['role', 'ready_to_work_status', 'verified_badge_status'], 'users_role_badges_idx');
            });
        } catch (\Exception $e) { /* index already exists — skip */ }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->index(['role', 'current_city'], 'users_role_city_idx');
            });
        } catch (\Exception $e) { /* index already exists — skip */ }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->index('worker_type_id', 'users_worker_type_id_idx');
            });
        } catch (\Exception $e) { /* index already exists — skip */ }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['worker_type_id']);
            $table->dropColumn([
                'onboarding_step',
                'selfie_file_url',
                'selfie_verified_at',
                'verified_badge_status',
                'verified_badge_updated_at',
                'ready_to_work_status',
                'ready_to_work_updated_at',
                'sponsorship_required',
                'employer_self_check_required',
                'available_date',
                'expected_salary',
                'worker_type_id',
                'unified_business_number',
            ]);
        });
    }
};
