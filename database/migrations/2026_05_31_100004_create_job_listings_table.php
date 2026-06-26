<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('employer_name');
            $table->enum('employer_type', ['company', 'factory', 'family_care', 'agency']);
            $table->string('location');
            $table->string('salary');
            $table->enum('salary_period', ['Month', 'Day', 'Hour'])->default('Month');
            $table->json('tags')->nullable();
            $table->string('category');
            $table->text('description')->nullable();
            $table->text('duties')->nullable();
            $table->text('requirements')->nullable();
            $table->text('benefits')->nullable();
            $table->boolean('is_urgent')->default(false);
            $table->enum('status', ['draft', 'submitted_for_review', 'published', 'paused', 'closed'])->default('published');
            $table->enum('risk_level', ['low', 'medium', 'high'])->default('low');
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['category', 'location']);
            $table->index('status');
            $table->index('is_urgent');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};
