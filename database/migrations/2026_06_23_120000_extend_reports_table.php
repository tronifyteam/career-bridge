<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // Extend report_type to include 'employer'
            $table->string('report_type')->change(); // convert enum → string for flexibility
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium')->after('report_type');
            $table->timestamp('resolved_at')->nullable()->after('admin_note');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['severity', 'resolved_at']);
        });
    }
};
