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
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ad_package_id')->constrained()->onDelete('cascade');
            $table->string('type'); // 'banner' or 'sponsored_job'
            $table->unsignedBigInteger('job_id')->nullable(); // Only for sponsored_job
            $table->string('title')->nullable(); // For banner
            $table->string('image_url')->nullable(); // For banner
            $table->string('target_url')->nullable(); // Link when banner clicked
            $table->enum('status', ['pending', 'active', 'paused', 'expired', 'rejected'])->default('pending');
            $table->datetime('starts_at')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->integer('impressions_count')->default(0);
            $table->integer('clicks_count')->default(0);
            $table->text('admin_note')->nullable();
            $table->timestamps();

            // Setup foreign key manually for job_id because it's named job_listings
            $table->foreign('job_id')->references('id')->on('job_listings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
