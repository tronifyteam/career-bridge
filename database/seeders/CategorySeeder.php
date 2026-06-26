<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Seed categories matching MockJobs.categories from Flutter app.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Manufacturing', 'icon' => 'precision_manufacturing'],
            ['name' => 'Construction', 'icon' => 'construction'],
            ['name' => 'Domestic Care', 'icon' => 'home_health'],
            ['name' => 'Logistics', 'icon' => 'local_shipping'],
            ['name' => 'Agriculture', 'icon' => 'agriculture'],
            ['name' => 'Fisheries', 'icon' => 'sailing'],
            ['name' => 'Hospitality', 'icon' => 'hotel'],
            ['name' => 'Technology', 'icon' => 'computer'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'icon' => $cat['icon'],
                ]
            );
        }
    }
}
