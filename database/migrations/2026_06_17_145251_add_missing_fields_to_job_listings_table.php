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
        Schema::table('job_listings', function (Blueprint $table) {
            $table->string('employment_type')->nullable();
            $table->text('working_hours_and_rest_days')->nullable();
            $table->integer('worker_count')->nullable();
            $table->string('employment_period')->nullable();
            $table->text('dormitory_meals_deductions')->nullable();
            $table->string('contact_method')->nullable();
            $table->boolean('mask_contact_info')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropColumn([
                'employment_type',
                'working_hours_and_rest_days',
                'worker_count',
                'employment_period',
                'dormitory_meals_deductions',
                'contact_method',
                'mask_contact_info'
            ]);
        });
    }
};
