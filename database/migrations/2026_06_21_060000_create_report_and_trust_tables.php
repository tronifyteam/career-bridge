<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('job_id')->nullable()->constrained('job_listings')->cascadeOnDelete();
            $table->foreignId('chat_message_id')->nullable()->constrained('chat_messages')->nullOnDelete();
            
            $table->enum('report_type', ['user', 'job', 'chat']);
            $table->string('reason');
            $table->text('description')->nullable();
            $table->string('evidence_url')->nullable();
            
            $table->enum('status', ['pending', 'in_review', 'resolved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            
            $table->timestamps();
        });

        Schema::create('violation_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('report_id')->nullable()->constrained('reports')->nullOnDelete();
            $table->string('violation_type');
            $table->text('description')->nullable();
            $table->integer('points_deducted')->default(0);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('trust_score')->default(100)->after('role');
            $table->integer('violation_count')->default(0)->after('trust_score');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['trust_score', 'violation_count']);
        });
        Schema::dropIfExists('violation_histories');
        Schema::dropIfExists('reports');
    }
};
