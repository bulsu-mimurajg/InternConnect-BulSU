<?php

namespace Database\Seeders;

use App\Models\Adviser;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DefenseFlow extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Roles
        $this->call(RolePermissionSeeder::class);

        // SIP Coordinator
        User::factory()->admin()->create([
            'username' => 'faye',
            'email' => 'faye@example.com',
            'status' => 'verified',
            'password' => bcrypt('password'),
        ]);

        // Sections
        $sections = [
        '4E-G1', '4E-G2',
        ];

        foreach ($sections as $sectionName) {
            Section::create([
                'section_name' => $sectionName,
                'status' => 'active'
            ]);
        }

        // Adviser
        User::factory()->adviser()->create([
            'username' => 'emman',
            'email' => 'emman@example.com',
            'status' => 'verified',
            'password' => bcrypt('password'),
        ]);

        User::factory()->adviser()->create([
            'username' => 'juana',
            'email' => 'juana@example.com',
            'status' => 'verified',
            'password' => bcrypt('password'),
        ]);

        $emmanUser = User::where('username', 'emman')->first();
        $juanaUser = User::where('username', 'juana')->first();

        $sections = Section::where('status', 'active')->get();

        $advisers = [
            [
                'adviser_fname' => 'Emmanuel',
                'adviser_lname' => 'Santos',
                'is_active' => true,
                'section_ids' => [$sections->first()->section_id ?? 1], // Single section
                'user_id' => $emmanUser->id ?? 2,
            ],
            [
                'adviser_fname' => 'Juana',
                'adviser_lname' => 'Cruz',
                'is_active' => true,
                'section_ids' => [$sections->last()->section_id ?? 2], // Single section
                'user_id' => $juanaUser->id ?? 3,
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

        // Student Assessment
        $this->call(
            CategorySeeder::class,
        );
    }
}
