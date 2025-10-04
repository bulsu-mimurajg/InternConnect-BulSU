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

        $this->call(CategorySeeder::class);
        $this->call(SectionSeeder::class);

        // Create student record for clairo after sections are seeded
        $section = \App\Models\Section::where('section_name', 'BSIT-4A')->first();
        if ($section) {
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
        $this->call(ExtendedInternshipSeeder::class);
        $this->call(StudentSeeder::class);
        $this->call(ExtendedStudentSeeder::class);
        $this->call(AcademeAccountSeeder::class);
        $this->call(InternshipCriteriaSeeder::class);
        $this->call(StudentScoreSeeder::class);
        $this->call(StudentMatchSeeder::class);
        $this->call(UnplacedStudentDemoSeeder::class);
        $this->call(PlacementSeeder::class);
        $this->call(AdviserSeeder::class);
        $this->call(EndorsementPlacementSeeder::class);
        $this->call(DeadlineSeeder::class);
        $this->call(AdditionalInfoSeeder::class);

        // Ensure all endorsements have corresponding notifications
        $this->call(NotificationSeeder::class);
    }
}
