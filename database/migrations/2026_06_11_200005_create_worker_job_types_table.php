<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_job_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('job_type_id')->constrained('job_types');
            $table->unsignedSmallInteger('years_of_experience')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'job_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_job_types');
    }
};
