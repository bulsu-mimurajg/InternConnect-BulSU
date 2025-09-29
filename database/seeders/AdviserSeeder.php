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
        
        // Get all available sections
        $sections = Section::where('status', 'active')->get();

        $advisers = [
            [
                'adviser_fname' => 'Emmanuel',
                'adviser_lname' => 'Santos',
                'is_active' => true,
                'section_ids' => [$sections->first()->section_id ?? 1], // Single section
                'user_id' => $emmanUser->id ?? 1,
            ],
            [
                'adviser_fname' => 'Maria',
                'adviser_lname' => 'Garcia',
                'is_active' => true,
                'section_ids' => $sections->take(2)->pluck('section_id')->toArray(), // Multiple sections
                'user_id' => null, // Will be created as a new user
            ],
            [
                'adviser_fname' => 'John',
                'adviser_lname' => 'Doe',
                'is_active' => true,
                'section_ids' => [$sections->skip(2)->first()->section_id ?? 3], // Single section
                'user_id' => null, // Will be created as a new user
            ],
            [
                'adviser_fname' => 'Sarah',
                'adviser_lname' => 'Wilson',
                'is_active' => true,
                'section_ids' => $sections->skip(1)->take(3)->pluck('section_id')->toArray(), // Multiple sections
                'user_id' => null, // Will be created as a new user
            ],
            [
                'adviser_fname' => 'Michael',
                'adviser_lname' => 'Johnson',
                'is_active' => false,
                'section_ids' => $sections->pluck('section_id')->toArray(), // All sections
                'user_id' => null, // Will be created as a new user
            ],
        ];

        foreach ($advisers as $adviserData) {
            // Extract section_ids before creating adviser
            $sectionIds = $adviserData['section_ids'];
            unset($adviserData['section_ids']);

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

            // Create the adviser
            $adviser = Adviser::create($adviserData);

            // Attach sections to the adviser
            $adviser->sections()->attach($sectionIds);
        }
    }
}
