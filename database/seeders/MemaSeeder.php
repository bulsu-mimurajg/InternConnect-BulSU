<?php

namespace Database\Seeders;

use App\Models\Adviser;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        User::factory()->admin()->create([
            'username' => 'faye',
            'email' => 'faye@example.com',
            'status' => 'verified',
            'password' => bcrypt('password'),
        ]);

        $emmanUser = User::factory()->adviser()->create([
            'username' => 'emman',
            'email' => 'emman@example.com',
            'status' => 'verified',
            'password' => bcrypt('password'),
        ]);

        $this->call(CategorySeeder::class);
        $this->call(SectionSeeder::class);

//        $this->call(StudentSeeder::class);
//        $this->call(ExtendedStudentSeeder::class);
//        $this->call(AcademeAccountSeeder::class);
//        $this->call(StudentScoreSeeder::class);
//        $this->call(StudentMatchSeeder::class);

        // Get all available sections
        $sections = Section::where('status', 'active')->get();

        $advisers = [
            [
                'adviser_fname' => 'Emmanuel',
                'adviser_lname' => 'Santos',
                'is_active' => true,
                'section_ids' => [$sections->first()->section_id ?? 1], // Single section
                'user_id' => $emmanUser->id,
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

//        $this->call(DeadlineSeeder::class);
        $this->call(AdditionalInfoSeeder::class);

    }

    private function getOrCreateSeasonForManualStudents(): \App\Models\InternshipSeason
    {
        // First, try to find an existing season
        $season = \App\Models\InternshipSeason::first();

        if ($season) {
            return $season;
        }

        // If no season exists, create a default one
        return \App\Models\InternshipSeason::create([
            'name' => 'AY 2024-2025 First Semester (Manual Students)',
            'start_date' => now()->subMonths(3),
            'end_date' => now()->addMonths(3),
            'status' => 'inactive', // Start as inactive
        ]);
    }

}
