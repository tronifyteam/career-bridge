<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages');
            $table->enum('proficiency_level', ['basic', 'intermediate', 'advanced', 'fluent'])->default('basic');
            $table->timestamps();

            $table->unique(['user_id', 'language_id']); // prevent duplicates
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_languages');
    }
};
