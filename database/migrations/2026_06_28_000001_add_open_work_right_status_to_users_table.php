<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Status of the open work right claim for white_collar workers.
            // null    = never claimed
            // pending = document uploaded, awaiting admin approval
            // approved = admin approved; sponsorship_required will be set false
            // rejected = admin rejected; sponsorship_required remains true
            $table->string('open_work_right_status')->nullable()->after('sponsorship_required');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('open_work_right_status');
        });
    }
};
