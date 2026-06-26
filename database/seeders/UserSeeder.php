<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the users table with the same mock data as the Flutter app.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Maria Santos',
                'full_name' => 'Maria Santos',
                'email' => 'worker@2ne5.tw',
                'password' => Hash::make('Password123!'),
                'role' => 'worker',
                'profile_completed' => true,
                'nationality' => 'Philippines',
                'current_city' => 'Taoyuan City',
                'cv_url' => 'https://example.com/cvs/maria_santos.pdf',
                'preferred_language' => 'Tagalog',
            ],
            [
                'name' => 'David Chen',
                'full_name' => 'David Chen',
                'email' => 'company@2ne5.tw',
                'password' => Hash::make('Password123!'),
                'role' => 'company',
                'profile_completed' => true,
                'company_name' => 'Taiwan Tech Corp',
                'industry' => 'Technology',
                'verification_status' => 'basic_verified',
            ],
            [
                'name' => 'James Lin',
                'full_name' => 'James Lin',
                'email' => 'factory@2ne5.tw',
                'password' => Hash::make('Password123!'),
                'role' => 'factory',
                'profile_completed' => true,
                'company_name' => 'Hsinchu Manufacturing',
                'industry' => 'Manufacturing',
                'verification_status' => 'manually_verified',
            ],
            [
                'name' => 'Lisa Wang',
                'full_name' => 'Lisa Wang',
                'email' => 'care@2ne5.tw',
                'password' => Hash::make('Password123!'),
                'role' => 'family_care',
                'profile_completed' => true,
                'company_name' => 'Wang Family',
                'industry' => 'Domestic Care',
                'verification_status' => 'manually_verified',
            ],
            [
                'name' => 'Robert Huang',
                'full_name' => 'Robert Huang',
                'email' => 'agency@2ne5.tw',
                'password' => Hash::make('Password123!'),
                'role' => 'agency',
                'profile_completed' => true,
                'company_name' => 'Global Staffing Agency',
                'industry' => 'Recruitment',
                'verification_status' => 'manually_verified',
                'license_number' => 'LIC-TW-889977',
            ],
            // Extra workers for realistic data
            [
                'name' => 'Nguyen Thi Lan',
                'full_name' => 'Nguyen Thi Lan',
                'email' => 'nguyen.lan@email.com',
                'password' => Hash::make('Password123!'),
                'role' => 'worker',
                'profile_completed' => true,
                'nationality' => 'Vietnam',
                'current_city' => 'Taipei',
                'cv_url' => 'https://example.com/cvs/nguyen_lan.pdf',
                'preferred_language' => 'Vietnamese',
            ],
            [
                'name' => 'Siti Rahayu',
                'full_name' => 'Siti Rahayu',
                'email' => 'siti.rahayu@email.com',
                'password' => Hash::make('Password123!'),
                'role' => 'worker',
                'profile_completed' => true,
                'nationality' => 'Indonesia',
                'current_city' => 'Kaohsiung',
                'cv_url' => 'https://example.com/cvs/siti_rahayu.pdf',
                'preferred_language' => 'Indonesian',
            ],
            [
                'name' => 'Somchai Prasert',
                'full_name' => 'Somchai Prasert',
                'email' => 'somchai.p@email.com',
                'password' => Hash::make('Password123!'),
                'role' => 'worker',
                'profile_completed' => true,
                'nationality' => 'Thailand',
                'current_city' => 'Taichung',
                'cv_url' => 'https://example.com/cvs/somchai_prasert.pdf',
                'preferred_language' => 'Thai',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
