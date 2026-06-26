<?php

namespace Database\Seeders;

use App\Models\JobType;
use Illuminate\Database\Seeder;

class JobTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['job_type_name' => 'Factory / Manufacturing',       'slug' => 'factory',           'description' => 'Assembly line, production, quality control'],
            ['job_type_name' => 'Care & Nursing',                'slug' => 'care_nursing',       'description' => 'Elderly care, home nursing, childcare'],
            ['job_type_name' => 'Agriculture & Fishery',         'slug' => 'agriculture',        'description' => 'Farming, fishing, aquaculture'],
            ['job_type_name' => 'Construction',                  'slug' => 'construction',       'description' => 'Building, civil works, renovation'],
            ['job_type_name' => 'F&B / Restaurant',              'slug' => 'fnb',                'description' => 'Cook, waiter, kitchen staff'],
            ['job_type_name' => 'Retail / Sales',                'slug' => 'retail_sales',       'description' => 'Shop assistant, cashier, sales rep'],
            ['job_type_name' => 'IT / Tech',                     'slug' => 'it_tech',            'description' => 'Software, hardware, IT support'],
            ['job_type_name' => 'Education / Teaching',          'slug' => 'education',          'description' => 'English teacher, tutor, curriculum'],
            ['job_type_name' => 'Office / Admin',                'slug' => 'office_admin',       'description' => 'Data entry, secretary, customer service'],
            ['job_type_name' => 'Driver / Logistics',            'slug' => 'driver_logistics',   'description' => 'Truck driver, delivery, warehouse'],
            ['job_type_name' => 'Hospitality / Hotel',           'slug' => 'hospitality',        'description' => 'Hotel staff, housekeeping, concierge'],
            ['job_type_name' => 'Cleaning / Sanitation',         'slug' => 'cleaning',           'description' => 'Office cleaning, facility maintenance'],
            ['job_type_name' => 'Healthcare / Medical',          'slug' => 'healthcare',         'description' => 'Nurse, lab tech, pharmacist, hospital admin'],
            ['job_type_name' => 'Translation / Interpretation',  'slug' => 'translation',        'description' => 'Bilingual, interpreter, document translation'],
            ['job_type_name' => 'Design / Creative',             'slug' => 'design_creative',    'description' => 'Graphic design, video, photography'],
            ['job_type_name' => 'Other',                         'slug' => 'other',              'description' => 'Other job categories'],
        ];

        foreach ($types as $type) {
            JobType::updateOrCreate(['slug' => $type['slug']], $type);
        }
    }
}
