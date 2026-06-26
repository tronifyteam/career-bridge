<?php

namespace Database\Seeders;

use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    /**
     * Seed jobs matching MockJobs.jobs from the Flutter app exactly.
     */
    public function run(): void
    {
        // Map jobs to employers by role type
        $factoryUser = User::where('role', 'factory')->first();
        $companyUser = User::where('role', 'company')->first();
        $familyCareUser = User::where('role', 'family_care')->first();
        $agencyUser = User::where('role', 'agency')->first();

        $jobs = [
            [
                'employer_id' => $factoryUser->id,
                'title' => 'Electronic Assembly Operator',
                'employer_name' => 'TSMC',
                'employer_type' => 'factory',
                'location' => 'Hsinchu',
                'salary' => 'NT$ 35,000 - 45,000',
                'salary_period' => 'Month',
                'tags' => ['Verified Factory', 'Dormitory Provided'],
                'category' => 'Manufacturing',
                'description' => "Join TSMC, the world's leading semiconductor foundry. We are looking for dedicated Electronic Assembly Operators to work in our state-of-the-art fabrication facilities in Hsinchu Science Park.",
                'duties' => "• Operate and monitor automated assembly equipment\n• Perform quality inspections on assembled components\n• Follow clean room protocols and safety procedures\n• Document production data and report anomalies\n• Participate in continuous improvement activities",
                'requirements' => "• Minimum 1 year manufacturing experience\n• Ability to work rotating shifts (day/night)\n• Good eyesight and manual dexterity\n• Basic understanding of electronic components\n• Willingness to learn and follow SOPs",
                'benefits' => "• Free dormitory accommodation\n• Meals provided (3 meals/day)\n• Health insurance coverage\n• Annual performance bonus\n• Overtime pay at 1.5x rate",
                'is_urgent' => false,
                'status' => 'published',
                'posted_at' => '2026-05-28 09:00:00',
            ],
            [
                'employer_id' => $familyCareUser->id,
                'title' => 'Home Caregiver',
                'employer_name' => 'Chen Family',
                'employer_type' => 'family_care',
                'location' => 'Taipei',
                'salary' => 'NT$ 28,000 + Overtime',
                'salary_period' => 'Month',
                'tags' => ['Urgent', 'Food Included'],
                'category' => 'Domestic Care',
                'description' => 'The Chen family is looking for a compassionate and responsible caregiver to help care for an elderly family member in their Taipei home. Live-in position with private room provided.',
                'duties' => "• Assist with daily living activities (bathing, dressing, feeding)\n• Administer medication on schedule\n• Accompany to medical appointments\n• Light housekeeping and meal preparation\n• Provide companionship and emotional support",
                'requirements' => "• Previous caregiving experience preferred\n• Basic Mandarin communication skills\n• Patient and compassionate personality\n• Willingness to live-in\n• Valid ARC (Alien Resident Certificate)",
                'benefits' => "• Private room with AC\n• 3 meals provided daily\n• Weekly day off\n• NT\$ 2,000 travel allowance/month\n• Year-end bonus",
                'is_urgent' => true,
                'status' => 'published',
                'posted_at' => '2026-05-30 14:00:00',
            ],
            [
                'employer_id' => $companyUser->id,
                'title' => 'Construction Worker',
                'employer_name' => 'Taiwan Build Co.',
                'employer_type' => 'company',
                'location' => 'Taichung',
                'salary' => 'NT$ 1,500 - 2,000',
                'salary_period' => 'Day',
                'tags' => ['Weekly Pay'],
                'category' => 'Construction',
                'description' => 'Taiwan Build Co. is hiring construction workers for a large residential development project in Taichung. Weekly pay with opportunity for overtime.',
                'duties' => "• Assist with concrete pouring and formwork\n• Carry and distribute materials on site\n• Operate basic construction tools\n• Follow site safety regulations\n• Clean and maintain work areas",
                'requirements' => "• Physical fitness and stamina\n• Previous construction experience is a plus\n• Ability to work outdoors in various weather\n• Safety awareness\n• Team player",
                'benefits' => "• Weekly cash payment\n• Safety equipment provided\n• Overtime available\n• Transportation to/from site\n• Performance bonus for project completion",
                'is_urgent' => false,
                'status' => 'published',
                'posted_at' => '2026-05-27 10:00:00',
            ],
            [
                'employer_id' => $factoryUser->id,
                'title' => 'CNC Machine Operator',
                'employer_name' => 'Precision Parts Ltd.',
                'employer_type' => 'factory',
                'location' => 'Taoyuan',
                'salary' => 'NT$ 32,000 - 38,000',
                'salary_period' => 'Month',
                'tags' => ['Verified Factory', 'Training Provided'],
                'category' => 'Manufacturing',
                'description' => 'Precision Parts Ltd. is seeking skilled CNC Machine Operators for our Taoyuan factory. Full training provided for the right candidates.',
                'duties' => "• Set up and operate CNC milling and turning machines\n• Read and interpret technical drawings\n• Perform tool changes and machine calibration\n• Inspect finished parts with measuring instruments\n• Maintain machine cleanliness and report malfunctions",
                'requirements' => "• Technical diploma or equivalent preferred\n• Basic understanding of metalworking\n• Ability to read simple technical drawings\n• Attention to detail and precision\n• Willingness to work overtime when needed",
                'benefits' => "• Dormitory available (NT\$ 2,500/month deduction)\n• Skill-based pay increases\n• Health and accident insurance\n• Year-end and Mid-Autumn bonuses\n• Free Mandarin language classes",
                'is_urgent' => false,
                'status' => 'published',
                'posted_at' => '2026-05-25 08:30:00',
            ],
            [
                'employer_id' => $companyUser->id,
                'title' => 'Warehouse Packer',
                'employer_name' => 'LogiTech Fulfillment',
                'employer_type' => 'company',
                'location' => 'New Taipei City',
                'salary' => 'NT$ 27,000 - 30,000',
                'salary_period' => 'Month',
                'tags' => ['Night Shift Available', 'Immediate Start'],
                'category' => 'Logistics',
                'description' => 'LogiTech Fulfillment is looking for warehouse packers for our e-commerce distribution center. Fast-paced environment with immediate start.',
                'duties' => "• Pick, pack, and label orders accurately\n• Scan barcodes and update inventory system\n• Organize and maintain warehouse sections\n• Load and unload delivery trucks\n• Meet daily packing targets",
                'requirements' => "• Ability to stand for extended periods\n• Basic smartphone/scanner operation\n• Attention to detail for order accuracy\n• Ability to lift up to 20kg\n• Flexible schedule (day/night shifts)",
                'benefits' => "• Night shift premium +NT\$ 3,000\n• Free shuttle bus from MRT station\n• Meal allowance NT\$ 100/day\n• Monthly attendance bonus\n• Group insurance",
                'is_urgent' => false,
                'status' => 'published',
                'posted_at' => '2026-05-29 11:00:00',
            ],
            [
                'employer_id' => $familyCareUser->id,
                'title' => 'Elderly Care Assistant',
                'employer_name' => 'Lin Family',
                'employer_type' => 'family_care',
                'location' => 'Kaohsiung',
                'salary' => 'NT$ 26,000 + Bonus',
                'salary_period' => 'Month',
                'tags' => ['Live-in', 'Mandarin Required'],
                'category' => 'Domestic Care',
                'description' => 'Kind and patient caregiver needed for 78-year-old grandmother in Kaohsiung. Live-in position with good working conditions.',
                'duties' => "• Daily care assistance (hygiene, mobility)\n• Prepare nutritious meals\n• Light physiotherapy exercises\n• Accompany on walks and outings\n• Keep living areas clean and organized",
                'requirements' => "• Caregiving certification preferred\n• Conversational Mandarin required\n• Patience and empathy\n• Non-smoker\n• References from previous employer",
                'benefits' => "• Private room and bathroom\n• All meals included\n• Monthly phone allowance\n• Quarterly performance bonus\n• Annual return ticket assistance",
                'is_urgent' => false,
                'status' => 'published',
                'posted_at' => '2026-05-26 16:00:00',
            ],
        ];

        foreach ($jobs as $jobData) {
            Job::updateOrCreate(
                [
                    'employer_id' => $jobData['employer_id'],
                    'title' => $jobData['title']
                ],
                $jobData
            );
        }
    }
}
