<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Seed cities matching MockJobs.cities from Flutter app.
     */
    public function run(): void
    {
        $cities = [
            ['name' => 'Taipei City', 'region' => 'Northern Taiwan'],
            ['name' => 'New Taipei City', 'region' => 'Northern Taiwan'],
            ['name' => 'Taoyuan City', 'region' => 'Northern Taiwan'],
            ['name' => 'Taichung City', 'region' => 'Central Taiwan'],
            ['name' => 'Tainan City', 'region' => 'Southern Taiwan'],
            ['name' => 'Kaohsiung City', 'region' => 'Southern Taiwan'],
            ['name' => 'Keelung City', 'region' => 'Northern Taiwan'],
            ['name' => 'Hsinchu City', 'region' => 'Northern Taiwan'],
            ['name' => 'Chiayi City', 'region' => 'Southern Taiwan'],
            ['name' => 'Hsinchu County', 'region' => 'Northern Taiwan'],
            ['name' => 'Miaoli County', 'region' => 'Central Taiwan'],
            ['name' => 'Changhua County', 'region' => 'Central Taiwan'],
            ['name' => 'Nantou County', 'region' => 'Central Taiwan'],
            ['name' => 'Yunlin County', 'region' => 'Central Taiwan'],
            ['name' => 'Chiayi County', 'region' => 'Southern Taiwan'],
            ['name' => 'Pingtung County', 'region' => 'Southern Taiwan'],
            ['name' => 'Yilan County', 'region' => 'Eastern Taiwan'],
            ['name' => 'Hualien County', 'region' => 'Eastern Taiwan'],
            ['name' => 'Taitung County', 'region' => 'Eastern Taiwan'],
            ['name' => 'Penghu County', 'region' => 'Outlying Islands'],
            ['name' => 'Kinmen County', 'region' => 'Outlying Islands'],
            ['name' => 'Lienchiang County', 'region' => 'Outlying Islands'],
            ['name' => 'Other', 'region' => 'Other'],
        ];

        foreach ($cities as $city) {
            City::updateOrCreate(
                ['name' => $city['name']],
                ['region' => $city['region']]
            );
        }
    }
}
