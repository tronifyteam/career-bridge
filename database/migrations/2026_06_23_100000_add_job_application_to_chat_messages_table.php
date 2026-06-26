<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            // Link chat messages back to the job & application that started the conversation.
            // Nullable because existing messages pre-date this field.
            $table->unsignedBigInteger('job_id')->nullable()->after('cv_data');
            $table->unsignedBigInteger('application_id')->nullable()->after('job_id');

            $table->foreign('job_id')->references('id')->on('job_listings')->nullOnDelete();
            $table->foreign('application_id')->references('id')->on('job_applications')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropForeign(['job_id']);
            $table->dropForeign(['application_id']);
            $table->dropColumn(['job_id', 'application_id']);
        });
    }
};
