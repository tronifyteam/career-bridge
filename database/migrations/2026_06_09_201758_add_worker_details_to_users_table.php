<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('worker_type')->nullable()->after('role');
            $table->string('current_work_status')->nullable()->after('worker_type');
            $table->json('language_abilities')->nullable()->after('skills');
            $table->boolean('is_cv_public')->default(true)->after('cv_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'worker_type',
                'current_work_status',
                'language_abilities',
                'is_cv_public',
            ]);
        });
    }
};
