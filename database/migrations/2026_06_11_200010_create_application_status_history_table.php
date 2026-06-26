<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('job_applications')->cascadeOnDelete();
            // Snapshot of worker status at time of application
            $table->string('verified_badge_status', 50)->nullable();
            $table->string('ready_to_work_status', 50)->nullable();
            $table->boolean('sponsorship_required')->nullable();
            $table->boolean('employer_self_check_required')->nullable();
            $table->string('worker_nationality', 100)->nullable();
            $table->string('worker_type_slug', 100)->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_status_history');
    }
};
