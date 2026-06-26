<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translation_logs', function (Blueprint $table) {
            $table->id();

            // Link back to the chat message that was translated
            $table->unsignedBigInteger('chat_message_id')->index();
            $table->foreign('chat_message_id')->references('id')->on('chat_messages')->cascadeOnDelete();

            // Who triggered it
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            // Text
            $table->text('original_text');
            $table->text('translated_text');

            // Language codes (ISO 639-1, e.g. 'en', 'id', 'zh-TW')
            $table->string('source_language', 10)->nullable(); // auto-detected
            $table->string('target_language', 10);

            // How it was triggered
            $table->enum('trigger_type', ['auto', 'manual'])->default('manual');

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translation_logs');
    }
};
