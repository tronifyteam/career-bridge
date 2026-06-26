<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_types', function (Blueprint $table) {
            $table->id();
            $table->string('job_type_name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        DB::table('job_types')->insert([
            ['job_type_name' => 'Caregiver',           'slug' => 'caregiver',           'created_at' => now(), 'updated_at' => now()],
            ['job_type_name' => 'Factory Worker',      'slug' => 'factory_worker',      'created_at' => now(), 'updated_at' => now()],
            ['job_type_name' => 'Domestic Helper',     'slug' => 'domestic_helper',     'created_at' => now(), 'updated_at' => now()],
            ['job_type_name' => 'Nurse',               'slug' => 'nurse',               'created_at' => now(), 'updated_at' => now()],
            ['job_type_name' => 'Construction Worker', 'slug' => 'construction_worker', 'created_at' => now(), 'updated_at' => now()],
            ['job_type_name' => 'Restaurant / F&B',   'slug' => 'restaurant_fb',       'created_at' => now(), 'updated_at' => now()],
            ['job_type_name' => 'Retail / Sales',      'slug' => 'retail_sales',        'created_at' => now(), 'updated_at' => now()],
            ['job_type_name' => 'Driver',              'slug' => 'driver',              'created_at' => now(), 'updated_at' => now()],
            ['job_type_name' => 'IT / Software',       'slug' => 'it_software',         'created_at' => now(), 'updated_at' => now()],
            ['job_type_name' => 'Engineer',            'slug' => 'engineer',            'created_at' => now(), 'updated_at' => now()],
            ['job_type_name' => 'Teacher / Tutor',     'slug' => 'teacher_tutor',       'created_at' => now(), 'updated_at' => now()],
            ['job_type_name' => 'Cleaning / Janitorial','slug'=> 'cleaning',            'created_at' => now(), 'updated_at' => now()],
            ['job_type_name' => 'Agriculture',         'slug' => 'agriculture',         'created_at' => now(), 'updated_at' => now()],
            ['job_type_name' => 'Fishery',             'slug' => 'fishery',             'created_at' => now(), 'updated_at' => now()],
            ['job_type_name' => 'Other',               'slug' => 'other',               'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('job_types');
    }
};
