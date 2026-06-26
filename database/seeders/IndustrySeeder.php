<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Industry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class IndustrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $industries = [
            'Manufacturing',
            'Technology',
            'Construction',
            'Domestic Care',
            'Agriculture',
            'Fisheries',
            'Hospitality',
            'Recruitment',
            'Other',
        ];

        foreach ($industries as $name) {
            Industry::updateOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name)]
            );
        }
    }
}
