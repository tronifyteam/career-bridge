<?php

namespace Database\Seeders;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Database\Seeder;

class JobApplicationSeeder extends Seeder
{
    /**
     * Seed sample job applications for realistic demo data.
     */
    public function run(): void
    {
        $workers = User::workers()->get();
        $jobs = Job::all();

        if ($workers->isEmpty() || $jobs->isEmpty()) return;

        $applications = [
            // Maria Santos applies to TSMC and Caregiver
            [
                'job_id' => $jobs->where('title', 'Electronic Assembly Operator')->first()?->id,
                'user_id' => $workers->where('email', 'worker@2ne5.tw')->first()?->id,
                'status' => 'reviewed',
                'cover_letter' => 'I have 2 years of manufacturing experience in the Philippines and am eager to work at TSMC.',
                'applied_at' => '2026-05-29 10:30:00',
            ],
            [
                'job_id' => $jobs->where('title', 'Home Caregiver')->first()?->id,
                'user_id' => $workers->where('email', 'worker@2ne5.tw')->first()?->id,
                'status' => 'pending',
                'cover_letter' => 'I have experience caring for elderly family members and speak basic Mandarin.',
                'applied_at' => '2026-05-30 16:00:00',
            ],
            // Nguyen applies to Warehouse
            [
                'job_id' => $jobs->where('title', 'Warehouse Packer')->first()?->id,
                'user_id' => $workers->where('email', 'nguyen.lan@email.com')->first()?->id,
                'status' => 'accepted',
                'cover_letter' => 'I worked in logistics for 3 years in Vietnam. I am ready for immediate start.',
                'applied_at' => '2026-05-29 14:00:00',
            ],
            // Siti applies to CNC and Elderly Care
            [
                'job_id' => $jobs->where('title', 'CNC Machine Operator')->first()?->id,
                'user_id' => $workers->where('email', 'siti.rahayu@email.com')->first()?->id,
                'status' => 'pending',
                'cover_letter' => 'I have a technical diploma and experience with basic metalworking.',
                'applied_at' => '2026-05-28 09:00:00',
            ],
            [
                'job_id' => $jobs->where('title', 'Elderly Care Assistant')->first()?->id,
                'user_id' => $workers->where('email', 'siti.rahayu@email.com')->first()?->id,
                'status' => 'reviewed',
                'cover_letter' => 'I have caregiving certification and can speak Mandarin conversationally.',
                'applied_at' => '2026-05-27 11:00:00',
            ],
            // Somchai applies to Construction
            [
                'job_id' => $jobs->where('title', 'Construction Worker')->first()?->id,
                'user_id' => $workers->where('email', 'somchai.p@email.com')->first()?->id,
                'status' => 'pending',
                'cover_letter' => 'I have 5 years of construction experience in Thailand.',
                'applied_at' => '2026-05-28 15:00:00',
            ],
        ];

        foreach ($applications as $appData) {
            if ($appData['job_id'] && $appData['user_id']) {
                JobApplication::updateOrCreate(
                    [
                        'job_id' => $appData['job_id'],
                        'user_id' => $appData['user_id']
                    ],
                    $appData
                );
            }
        }
    }
}
