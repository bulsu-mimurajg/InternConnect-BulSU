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
        // Create HTE users with specific test accounts
        $hteUsers = [
            [
                'username' => 'hte_company1',
                'email' => 'hte1@example.com',
                'password' => bcrypt('password'),
            ],
            [
                'username' => 'hte_company2',
                'email' => 'hte2@example.com',
                'password' => bcrypt('password'),
            ],
            [
                'username' => 'hte_company3',
                'email' => 'hte3@example.com',
                'password' => bcrypt('password'),
            ],
            [
                'username' => 'hte_company4',
                'email' => 'hte4@example.com',
                'password' => bcrypt('password'),
            ],
            [
                'username' => 'hte_company5',
                'email' => 'hte5@example.com',
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

        // Create HTE records for the test users
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
            ],
            [
                'user_id' => User::where('username', 'hte_company2')->first()->id,
                'company_name' => 'DataFlow Analytics',
                'company_address' => '456 Data Street, Analytics Town, AT 67890',
                'company_email' => 'internships@dataflow.com',
                'cperson_fname' => 'Sarah',
                'cperson_lname' => 'Johnson',
                'cperson_position' => 'Talent Acquisition Specialist',
                'cperson_contactnum' => '+1-555-0102',
                'is_active' => true,
            ],
            [
                'user_id' => User::where('username', 'hte_company3')->first()->id,
                'company_name' => 'GreenTech Industries',
                'company_address' => '789 Eco Boulevard, Green City, GC 11111',
                'company_email' => 'careers@greentech.com',
                'cperson_fname' => 'Michael',
                'cperson_lname' => 'Brown',
                'cperson_position' => 'Recruitment Coordinator',
                'cperson_contactnum' => '+1-555-0103',
                'is_active' => true,
            ],
            [
                'user_id' => User::where('username', 'hte_company4')->first()->id,
                'company_name' => 'Creative Marketing Pro',
                'company_address' => '321 Creative Lane, Marketing City, MC 22222',
                'company_email' => 'hr@creativemarketing.com',
                'cperson_fname' => 'Emily',
                'cperson_lname' => 'Davis',
                'cperson_position' => 'HR Director',
                'cperson_contactnum' => '+1-555-0104',
                'is_active' => true,
            ],
            [
                'user_id' => User::where('username', 'hte_company5')->first()->id,
                'company_name' => 'FinanceFirst Bank',
                'company_address' => '654 Finance Avenue, Banking City, BC 33333',
                'company_email' => 'internships@financefirst.com',
                'cperson_fname' => 'David',
                'cperson_lname' => 'Wilson',
                'cperson_position' => 'Talent Manager',
                'cperson_contactnum' => '+1-555-0105',
                'is_active' => true,
            ],
        ];

        foreach ($hteData as $data) {
            HTE::firstOrCreate(
                ['user_id' => $data['user_id']],
                $data
            );
        }

        // Create additional random HTE records using factory
        HTE::factory(10)->create();
    }
}
