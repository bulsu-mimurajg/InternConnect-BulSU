<?php

namespace Database\Seeders;

use App\Models\Adviser;
use App\Models\User;
use App\Models\Section;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdviserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the user with username 'emman' (created in DatabaseSeeder)
        $emmanUser = User::where('username', 'emman')->first();
        
        // Get some sections to assign to advisers
        $sections = Section::where('status', 'active')->take(5)->get();

        $advisers = [
            [
                'adviser_fname' => 'Emmanuel',
                'adviser_lname' => 'Santos',
                'is_active' => true,
                'section_id' => $sections->first()->section_id ?? 1,
                'user_id' => $emmanUser->id ?? 1,
            ],
            [
                'adviser_fname' => 'Maria',
                'adviser_lname' => 'Garcia',
                'is_active' => true,
                'section_id' => $sections->skip(1)->first()->section_id ?? 2,
                'user_id' => null, // Will be created as a new user
            ],
            [
                'adviser_fname' => 'John',
                'adviser_lname' => 'Doe',
                'is_active' => true,
                'section_id' => $sections->skip(2)->first()->section_id ?? 3,
                'user_id' => null, // Will be created as a new user
            ],
            [
                'adviser_fname' => 'Sarah',
                'adviser_lname' => 'Wilson',
                'is_active' => true,
                'section_id' => $sections->skip(3)->first()->section_id ?? 4,
                'user_id' => null, // Will be created as a new user
            ],
            [
                'adviser_fname' => 'Michael',
                'adviser_lname' => 'Brown',
                'is_active' => false,
                'section_id' => $sections->skip(4)->first()->section_id ?? 5,
                'user_id' => null, // Will be created as a new user
            ],
        ];

        foreach ($advisers as $adviserData) {
            // If no user_id is provided, create a new user for this adviser
            if (!$adviserData['user_id']) {
                $user = User::create([
                    'username' => strtolower($adviserData['adviser_fname'] . '.' . $adviserData['adviser_lname']),
                    'email' => strtolower($adviserData['adviser_fname'] . '.' . $adviserData['adviser_lname']) . '@example.com',
                    'password' => bcrypt('password'),
                    'status' => 'verified',
                ]);
                
                // Assign adviser role to the user
                $user->assignRole('adviser');
                
                $adviserData['user_id'] = $user->id;
            }

            Adviser::create($adviserData);
        }
    }
}
