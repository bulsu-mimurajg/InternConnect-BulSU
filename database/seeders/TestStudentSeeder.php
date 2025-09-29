<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\Section;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find an active section to assign the student to
        $section = Section::where('status', 'active')->first();
        
        if (!$section) {
            $this->command->warn('No active section found. Creating a test section...');
            $section = Section::create([
                'section_name' => 'TEST-2024',
                'status' => 'active',
            ]);
            $this->command->info("✅ Created test section: {$section->section_name}");
        }

        // Create test student user with unique username
        $timestamp = now()->format('YmdHis');
        $username = 'test-student-' . $timestamp;
        $email = 'test-student-' . $timestamp . '@example.com';
        
        $user = User::create([
            'username' => $username,
            'email' => $email,
            'password' => Hash::make('password123'),
            'status' => 'verified',
            'email_verified_at' => now(),
        ]);

        // Assign student role
        $user->assignRole('student');

        // Create student profile with shorter student number
        $studentNumber = 'TEST-' . substr($timestamp, -6); // Last 6 digits of timestamp
        
        $student = Student::create([
            'user_id' => $user->id,
            'student_number' => $studentNumber,
            'first_name' => 'Test',
            'last_name' => 'Student',
            'middle_name' => 'T',
            'phone' => '09123456789',
            'section_id' => $section->section_id,
            'specialization' => 'Software Development',
            'is_active' => true,
            'is_submit' => false, // Hasn't answered assessment yet
        ]);

        $this->command->info("✅ Created test student:");
        $this->command->info("   Email: {$email}");
        $this->command->info("   Password: password123");
        $this->command->info("   Student Number: {$studentNumber}");
        $this->command->info("   Section: {$section->section_name}");
        $this->command->info("   Assessment Status: Not Submitted");
        $this->command->info("   Endorsement Status: Not Endorsed");
        $this->command->info("");
        $this->command->info("🎯 Perfect for testing:");
        $this->command->info("   • Student deadline notifications");
        $this->command->info("   • Student placement notifications");
        $this->command->info("   • Assessment reminder emails");
        $this->command->info("   • Role-based notification filters");
    }
}
