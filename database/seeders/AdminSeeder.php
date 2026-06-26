<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@2ne5.tw'],
            [
                'name' => 'Super Admin',
                'full_name' => 'Super Admin 2ne5',
                'password' => Hash::make('Admin123!'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
