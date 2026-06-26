<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('full_name')->after('name')->nullable();
            $table->enum('role', ['worker', 'company', 'factory', 'family_care', 'agency'])->nullable()->after('email');
            $table->string('nationality')->nullable();
            $table->string('current_city')->nullable();
            $table->string('company_name')->nullable();
            $table->string('industry')->nullable();
            $table->boolean('profile_completed')->default(false);
            $table->string('avatar_url')->nullable();
            $table->string('phone')->nullable();
            
            // PRD specific fields
            $table->string('license_number')->nullable();
            $table->enum('verification_status', ['unverified', 'pending', 'basic_verified', 'manually_verified', 'rejected'])->default('unverified');
            $table->string('cv_url')->nullable();
            $table->string('preferred_language')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'full_name', 'role', 'nationality', 'current_city',
                'company_name', 'industry', 'profile_completed',
                'avatar_url', 'phone', 'license_number', 'verification_status',
                'cv_url', 'preferred_language'
            ]);
        });
    }
};
