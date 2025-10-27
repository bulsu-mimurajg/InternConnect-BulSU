<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(RolePermissionSeeder::class);

        User::factory()->admin()->create([
            'username' => 'faye',
            'email' => 'faye@example.com',
            'status' => 'verified',
            'password' => bcrypt('password'),
        ]);

        User::factory()->hte()->create([
            'username' => 'maria',
            'email' => 'maria@example.com',
            'status' => 'verified',
            'password' => bcrypt('password'),
        ]);

        User::factory()->adviser()->create([
            'username' => 'emman',
            'email' => 'emman@example.com',
            'status' => 'verified',
            'password' => bcrypt('password'),
        ]);

        $clairoUser = User::factory()->student()->create([
            'username' => 'clairo',
            'email' => 'clairo@example.com',
            'status' => 'verified',
            'password' => bcrypt('password'),
        ]);

        // Student::factory()->count(20)->create();

        // Seed categories and subcategories only; student assessment questions are seeded separately
        $this->call(CategoryOnlySeeder::class);
        // Seed HTE assessment criteria questions (Likert scale)
        $this->call(HTEAssessmentSeeder::class);
        $this->call(SectionSeeder::class);

        // Create student record for clairo after sections are seeded
        $section = \App\Models\Section::where('section_name', 'BSIT-4A')->first();
        if ($section) {
            // Get or create a season for the student
            $season = $this->getOrCreateSeasonForManualStudents();

            Student::create([
                'user_id' => $clairoUser->id,
                'student_number' => '2022100100',
                'first_name' => 'Clairo',
                'middle_name' => 'Test',
                'last_name' => 'Student',
                'phone' => '09123456792',
                'section_id' => $section->section_id,
                'specialization' => 'WMAD',
                'is_submit' => true, // Set to true so it appears in reports
                'is_placed' => false,
                'is_active' => true,
                'internship_season_id' => $season->id,
            ]);

            // Create academe account for clairo
            \App\Models\AcademeAccount::create([
                'user_id' => $clairoUser->id,
                'section_id' => $section->section_id,
            ]);
        }

        // Create unverified student following the complete registration flow
        $threeAG1Section = \App\Models\Section::where('section_name', '3A-G1')->first();
        if ($threeAG1Section) {
            // 1. Create user account (same as registration flow)
            $unverifiedStudentUser = User::create([
                'username' => '2024100123', // Student number format
                'email' => 'george.miller@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(), // Email verified
                'status' => 'unverified', // Needs adviser approval
            ]);

            // 2. Assign student role
            $unverifiedStudentUser->assignRole('student');

            // 3. Cache registration data (simulating what happens during registration)
            $registrationData = [
                'first_name' => 'George',
                'last_name' => 'Miller',
                'middle_name' => '',
                'username' => '2024100123',
                'email' => 'george.miller@example.com',
                'contact_number' => '09123456789',
                'password' => bcrypt('password'),
                'section_id' => $threeAG1Section->section_id,
                'specialization' => 'WMAD',
                'created_at' => now(),
            ];

            // Store registration data for when adviser approves (24 hours expiry)
            \Illuminate\Support\Facades\Cache::put("registration_data_{$unverifiedStudentUser->email}", $registrationData, now()->addHours(24));

            // 4. Create academe account (same way as registration)
            \App\Models\AcademeAccount::create([
                'user_id' => $unverifiedStudentUser->id,
                'section_id' => $threeAG1Section->section_id,
            ]);

            // 5. Create request record (same way as registration)
            \App\Models\Request::create([
                'stud_num' => $unverifiedStudentUser->username,
                'section_id' => $threeAG1Section->section_id,
            ]);

            // 6. Create notification for Emman (adviser) about new student needing approval
            $emmanUser = User::where('username', 'emman')->first();
            if ($emmanUser) {
                \App\Models\Notification::create([
                    'user_id' => $emmanUser->id,
                    'type' => 'student_approval_request',
                    'title' => 'New Student Registration',
                    'message' => 'George Miller has registered and is awaiting your approval.',
                    'data' => [
                        'adviser_id' => $emmanUser->id,
                        'student_username' => $unverifiedStudentUser->username,
                        'student_email' => $unverifiedStudentUser->email,
                        'student_name' => 'George Miller',
                        'section_id' => $threeAG1Section->section_id,
                        'section_name' => $threeAG1Section->section_name,
                    ],
                    'is_read' => false,
                ]);
            }
        }


        $this->call(HTESeeder::class);
        $this->call(InternshipSeeder::class);
        $this->call(StudentSeeder::class);
        $this->call(ExtendedStudentSeeder::class);
        $this->call(AcademeAccountSeeder::class);
        $this->call(InternshipCriteriaSeeder::class);
        $this->call(StudentScoreSeeder::class);
        $this->call(StudentMatchSeeder::class);
//        $this->call(UnplacedStudentDemoSeeder::class);
        $this->call(PlacementSeeder::class);
        $this->call(AdviserSeeder::class);
        $this->call(EndorsementPlacementSeeder::class);
        $this->call(DeadlineSeeder::class);
        $this->call(AdditionalInfoSeeder::class);

        // Associate existing students with the default season
        $this->associateStudentsWithDefaultSeason();

        // Ensure all endorsements have corresponding notifications
        $this->call(NotificationSeeder::class);
    }

    /**
     * Associate existing students with the default internship season
     */
    private function associateStudentsWithDefaultSeason(): void
    {
        // Find the default season created by DeadlineSeeder
        $defaultSeason = \App\Models\InternshipSeason::where('name', 'AY 2024-2025 First Semester (Default)')->first();

        if (!$defaultSeason) {
            $this->command->warn('Default season not found. Students will not be associated with any season.');
            return;
        }

        // Get all students that don't have a season assigned
        $studentsWithoutSeason = Student::whereNull('internship_season_id')->get();

        if ($studentsWithoutSeason->isEmpty()) {
            $this->command->info('All students already have seasons assigned.');
            return;
        }

        // Associate students with the default season
        $updatedCount = Student::whereNull('internship_season_id')
            ->update(['internship_season_id' => $defaultSeason->id]);

        $this->command->info("Associated {$updatedCount} students with default season: {$defaultSeason->name}");

        // Also associate the manually created students in DatabaseSeeder
        $this->associateManualStudentsWithSeason($defaultSeason);
    }

    /**
     * Associate manually created students (like Clairo) with the season
     */
    private function associateManualStudentsWithSeason(\App\Models\InternshipSeason $season): void
    {
        // Associate Clairo's student record
        $clairoStudent = Student::where('student_number', '2022100100')->first();
        if ($clairoStudent && !$clairoStudent->internship_season_id) {
            $clairoStudent->update(['internship_season_id' => $season->id]);
            $this->command->info("Associated Clairo's student record with season: {$season->name}");
        }

        // Associate George Miller's student record if it exists (after approval)
        $georgeStudent = Student::where('student_number', '2024100123')->first();
        if ($georgeStudent && !$georgeStudent->internship_season_id) {
            $georgeStudent->update(['internship_season_id' => $season->id]);
            $this->command->info("Associated George Miller's student record with season: {$season->name}");
        }
    }

    /**
     * Get or create a season for manually created students
     */
    private function getOrCreateSeasonForManualStudents(): \App\Models\InternshipSeason
    {
        // First, try to find an existing season
        $season = \App\Models\InternshipSeason::first();

        if ($season) {
            return $season;
        }

        // If no season exists, create a default one
        $season = \App\Models\InternshipSeason::create([
            'name' => 'AY 2024-2025 First Semester (Manual Students)',
            'start_date' => now()->subMonths(3),
            'end_date' => now()->addMonths(3),
            'status' => 'inactive', // Start as inactive
        ]);

        return $season;
    }
}
