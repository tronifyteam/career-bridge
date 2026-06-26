<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 50); // worker, employer, job_post, document, employer_staff
            $table->unsignedBigInteger('entity_id');
            $table->string('action', 50); // approved, rejected, suspended, reviewed, pending
            $table->text('notes')->nullable();
            $table->foreignId('verified_by')->constrained('users');
            $table->timestamp('verified_at')->useCurrent();

            $table->index(['entity_type', 'entity_id']);
            $table->index('verified_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_logs');
    }
};
