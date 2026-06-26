<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('language_code', 10)->unique();
            $table->string('language_name', 100);
            $table->timestamps();
        });

        DB::table('languages')->insert([
            ['language_code' => 'EN', 'language_name' => 'English',            'created_at' => now(), 'updated_at' => now()],
            ['language_code' => 'ID', 'language_name' => 'Indonesian',         'created_at' => now(), 'updated_at' => now()],
            ['language_code' => 'TL', 'language_name' => 'Tagalog',            'created_at' => now(), 'updated_at' => now()],
            ['language_code' => 'TH', 'language_name' => 'Thai',               'created_at' => now(), 'updated_at' => now()],
            ['language_code' => 'VI', 'language_name' => 'Vietnamese',         'created_at' => now(), 'updated_at' => now()],
            ['language_code' => 'JA', 'language_name' => 'Japanese',           'created_at' => now(), 'updated_at' => now()],
            ['language_code' => 'ZH', 'language_name' => 'Traditional Chinese','created_at' => now(), 'updated_at' => now()],
            ['language_code' => 'MY', 'language_name' => 'Malay',              'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
