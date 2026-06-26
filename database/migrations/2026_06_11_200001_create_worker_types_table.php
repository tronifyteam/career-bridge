<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_types', function (Blueprint $table) {
            $table->id();
            $table->string('worker_type_name', 100)->unique();
            $table->string('slug', 100)->unique(); // machine-readable key
            $table->text('description')->nullable();
            $table->boolean('requires_arc')->default(true);
            $table->boolean('auto_ready_to_work')->default(false); // true = no extra doc needed
            $table->boolean('eligible_to_work')->default(true); // false = Not Sure/No ARC
            $table->timestamps();
        });

        // Seed data — 7 types from ERD + ALUR.jpg
        DB::table('worker_types')->insert([
            [
                'worker_type_name' => 'Student ARC',
                'slug'             => 'student',
                'description'      => 'International student with ARC and part-time work permit',
                'requires_arc'     => true,
                'auto_ready_to_work' => false,
                'eligible_to_work' => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'worker_type_name' => 'Blue Collar Migrant Worker',
                'slug'             => 'blue_collar',
                'description'      => 'Migrant worker with specific work permit (blue collar)',
                'requires_arc'     => true,
                'auto_ready_to_work' => false,
                'eligible_to_work' => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'worker_type_name' => 'Professional / White Collar',
                'slug'             => 'white_collar',
                'description'      => 'Professional or white collar worker, may need sponsorship',
                'requires_arc'     => true,
                'auto_ready_to_work' => false,
                'eligible_to_work' => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'worker_type_name' => 'ARC Other / Open Work Right',
                'slug'             => 'arc_other',
                'description'      => 'ARC holder with open work rights, no extra doc needed',
                'requires_arc'     => true,
                'auto_ready_to_work' => true,
                'eligible_to_work' => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'worker_type_name' => 'APRC / Gold Card / Spouse with Work Right',
                'slug'             => 'aprc_gold_card',
                'description'      => 'APRC, Gold Card holder, or spouse with full work rights',
                'requires_arc'     => true,
                'auto_ready_to_work' => true,
                'eligible_to_work' => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'worker_type_name' => 'Taiwanese',
                'slug'             => 'taiwanese',
                'description'      => 'Taiwanese local worker, no ARC required',
                'requires_arc'     => false,
                'auto_ready_to_work' => true,
                'eligible_to_work' => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'worker_type_name' => 'Not Sure / No ARC',
                'slug'             => 'not_sure',
                'description'      => 'Undecided or no ARC — not eligible for ready to work status',
                'requires_arc'     => false,
                'auto_ready_to_work' => false,
                'eligible_to_work' => false,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_types');
    }
};
