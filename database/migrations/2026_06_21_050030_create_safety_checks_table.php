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
        Schema::create('safety_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('source_type', ['job', 'chat', 'screenshot']);
            $table->string('source_id')->nullable();          // job_id if source_type = job
            $table->text('input_text')->nullable();           // text analyzed (chat messages / job text)
            $table->string('image_url')->nullable();          // screenshot URL if source_type = screenshot
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical']);
            $table->json('result_json');                      // full AI response
            $table->string('language', 30)->default('English');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('safety_checks');
    }
};
