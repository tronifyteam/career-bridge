<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Nationality;
use Illuminate\Database\Seeder;

class NationalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nationalities = [
            ['name' => 'Indonesia', 'code' => 'ID'],
            ['name' => 'Philippines', 'code' => 'PH'],
            ['name' => 'Vietnam', 'code' => 'VI'],
            ['name' => 'Thailand', 'code' => 'TH'],
            ['name' => 'Myanmar', 'code' => 'MM'],
            ['name' => 'Cambodia', 'code' => 'KH'],
            ['name' => 'India', 'code' => 'IN'],
            ['name' => 'Other', 'code' => 'OTHER'],
        ];

        foreach ($nationalities as $nat) {
            Nationality::updateOrCreate(
                ['name' => $nat['name']],
                ['code' => $nat['code']]
            );
        }
    }
}
