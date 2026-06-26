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
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->enum('type', ['text', 'image', 'video', 'file'])->default('text')->after('message');
            $table->string('attachment_url')->nullable()->after('type');
            $table->string('attachment_name')->nullable()->after('attachment_url');
            $table->integer('attachment_size')->nullable()->after('attachment_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn(['type', 'attachment_url', 'attachment_name', 'attachment_size']);
        });
    }
};
