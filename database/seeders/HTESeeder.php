<?php

namespace Database\Seeders;

use App\Models\HTE;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HTESeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create HTE users with specific test accounts (reduced to minimum)
        $hteUsers = [
            [
                'username' => 'hte_company1',
                'email' => 'hte1@example.com',
                'status' => 'verified',
                'password' => bcrypt('password'),
            ],
        ];

        foreach ($hteUsers as $userData) {
            $user = User::firstOrCreate(
                ['username' => $userData['username']],
                $userData
            );
            if (!$user->hasRole('hte')) {
                $user->assignRole('hte');
            }
        }

        // Create HTE records for the test users (reduced to minimum)
        $hteData = [
            [
                'user_id' => User::where('username', 'hte_company1')->first()->id,
                'company_name' => 'TechCorp Solutions',
                'company_address' => '123 Innovation Drive, Tech City, TC 12345',
                'company_email' => 'hr@techcorp.com',
                'cperson_fname' => 'John',
                'cperson_lname' => 'Smith',
                'cperson_position' => 'HR Manager',
                'cperson_contactnum' => '+1-555-0101',
                'is_active' => true,
                'is_submit' => true,
            ],
        ];

        foreach ($hteData as $data) {
            HTE::firstOrCreate(
                ['user_id' => $data['user_id']],
                $data
            );
        }

        // Create HTE record for 'maria' user (created in DatabaseSeeder)
        $mariaUser = User::where('username', 'maria')->first();
        if ($mariaUser) {
            HTE::firstOrCreate(
                ['user_id' => $mariaUser->id],
                [
                    'user_id' => $mariaUser->id,
                    'company_name' => 'MariaTech Solutions',
                    'company_address' => '888 Innovation Plaza, Tech Hub District, Manila 1000',
                    'company_email' => 'maria@mariatech.com',
                    'cperson_fname' => 'Maria',
                    'cperson_lname' => 'Santos',
                    'cperson_position' => 'HR Director',
                    'cperson_contactnum' => '+63-917-123-4567',
                    'is_active' => true,
                    'is_submit' => true,
                ]
            );
        }

        // Note: Removed factory-generated HTE records as they are not used in any functionality
    }
}
