<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('job_applications')->cascadeOnDelete();
            $table->string('status', 30); // pending, viewed, shortlisted, accepted, rejected, cancelled
            $table->string('notes', 500)->nullable();      // employer notes or system message
            $table->string('changed_by', 20)->default('system'); // 'system', 'employer', 'worker'
            $table->timestamp('changed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_status_logs');
    }
};
