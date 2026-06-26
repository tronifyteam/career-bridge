<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Run order matters — users first, then jobs (which reference users),
     * then applications (which reference both users and jobs).
     */
    public function run(): void
    {
        $this->call([
            // ── 1. Master data (no FK deps) ─────────────────
            WorkerTypeSeeder::class,
            LanguageSeeder::class,
            NationalitySeeder::class,
            JobTypeSeeder::class,
            DocumentTypeSeeder::class,
            CategorySeeder::class,
            CitySeeder::class,
            IndustrySeeder::class,

            // ── 2. Users (depends on WorkerType) ─────────────
            AdminSeeder::class,
            UserSeeder::class,

            // ── 3. Jobs & Applications ────────────────────────
            JobSeeder::class,
            JobApplicationSeeder::class,
        ]);
    }
}
