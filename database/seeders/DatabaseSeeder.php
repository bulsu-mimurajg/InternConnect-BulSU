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
            'email' => 'mimuraschool@gmail.com',
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
            'email' => 'lesutemp@gmail.com',
            'status' => 'verified',
            'password' => bcrypt('password'),
        ]);

        // Create Juna - a student who needs adviser approval (pending verification)
        $junaUser = User::factory()->student()->create([
            'username' => 'juna',
            'email' => 'juna@example.com',
            'status' => 'unverified', // Unverified so she shows in pending students
            'password' => bcrypt('password'),
        ]);

        // Student::factory()->count(20)->create();

        $this->call(CategorySeeder::class);
        $this->call(SectionSeeder::class);

        // Create student record for clairo after sections are seeded
        $section = \App\Models\Section::where('section_name', 'BSIT-4A')->first();
        if ($section) {
            Student::create([
                'user_id' => $clairoUser->id,
                'student_number' => '2021-0004',
                'first_name' => 'Clairo',
                'middle_name' => 'Test',
                'last_name' => 'Student',
                'phone' => '09123456792',
                'section_id' => $section->section_id,
                'specialization' => 'WMAD',
                'is_submit' => true, // Set to true so it appears in reports
                'is_placed' => false,
                'is_active' => true,
            ]);

            // Create academe account for clairo
            \App\Models\AcademeAccount::create([
                'user_id' => $clairoUser->id,
                'section_id' => $section->section_id,
            ]);
        }

        // Get Emman's section (3A-G1) to assign Juna to the same section
        $emmanSection = \App\Models\Section::where('section_name', '3A-G1')->first();
        if ($emmanSection) {
            // Create academe account for Juna (pending student - no Student record yet)
            \App\Models\AcademeAccount::create([
                'user_id' => $junaUser->id,
                'section_id' => $emmanSection->section_id, // Same section as Emman (adviser)
            ]);

            // Create notification for Emman about Juna needing approval
            $emmanUser = User::where('username', 'emman')->first();
            if ($emmanUser) {
                \App\Models\Notification::create([
                    'user_id' => $emmanUser->id,
                    'type' => 'student_approval_request',
                    'title' => 'Student Approval Request',
                    'message' => 'Student Juna needs your approval for registration.',
                    'data' => [
                        'adviser_id' => $emmanUser->id,
                        'student_name' => 'Juna',
                        'section_id' => $emmanSection->section_id
                    ],
                    'is_read' => false,
                ]);
            }
        }

        $this->call(HTESeeder::class);
        $this->call(InternshipSeeder::class);
        $this->call(ExtendedInternshipSeeder::class);
        $this->call(StudentSeeder::class);
        $this->call(ExtendedStudentSeeder::class);
        $this->call(AcademeAccountSeeder::class);
        $this->call(InternshipCriteriaSeeder::class);
        $this->call(StudentScoreSeeder::class);
        $this->call(StudentMatchSeeder::class);
        $this->call(PlacementSeeder::class);
        $this->call(AdviserSeeder::class);
        $this->call(EndorsementPlacementSeeder::class);
        $this->call(DeadlineSeeder::class);
        $this->call(AdditionalInfoSeeder::class);

        // Ensure all endorsements have corresponding notifications
        $this->call(NotificationSeeder::class);
    }
}
