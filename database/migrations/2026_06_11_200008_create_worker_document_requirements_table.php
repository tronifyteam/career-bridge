<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_document_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('document_type_id')->constrained('document_types');
            $table->enum('upload_status', ['not_uploaded', 'uploaded', 'verified', 'rejected'])->default('not_uploaded');
            $table->foreignId('worker_document_id')->nullable()->constrained('worker_documents')->nullOnDelete();
            $table->date('required_by_date')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'document_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_document_requirements');
    }
};
