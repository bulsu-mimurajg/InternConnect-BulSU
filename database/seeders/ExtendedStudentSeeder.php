<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\User;
use App\Models\Section;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ExtendedStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if student role exists
        $studentRole = Role::where('name', 'student')->first();
        if (!$studentRole) {
            $this->command->warn('Student role not found. Please run RolePermissionSeeder first.');
            return;
        }

        // Get sections from database
        $availableSections = Section::whereIn('section_name', ['BSIT-4A', 'BSIT-4B', 'BSIT-4C'])->get();
        if ($availableSections->isEmpty()) {
            $this->command->warn('Required sections not found. Please run SectionSeeder first.');
            return;
        }

        // Note: Removed unused students as they are not referenced in any application functionality
        // Only clairo and juna from DatabaseSeeder are actually used
        $additionalStudents = [];

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($additionalStudents as $studentData) {
            // Check if student already exists
            $existingStudent = Student::where('student_number', $studentData['student_number'])->first();
            if ($existingStudent) {
                $skippedCount++;
                continue;
            }

            // Get section ID
            $section = $availableSections->where('section_name', $studentData['section_name'])->first();
            if (!$section) {
                $this->command->warn("Section {$studentData['section_name']} not found. Skipping student {$studentData['student_number']}.");
                continue;
            }

            // Create user account
            $user = User::create([
                'username' => strtolower(str_replace(' ', '.', $studentData['first_name'] . ' ' . $studentData['last_name'])),
                'email' => strtolower(str_replace(' ', '.', $studentData['first_name'] . ' ' . $studentData['last_name'])) . '@example.com',
                'password' => Hash::make('password123'),
                'status' => 'verified',
            ]);

            // Assign student role
            $user->assignRole('student');

            // Create student record
            Student::create([
                'user_id' => $user->id,
                'student_number' => $studentData['student_number'],
                'first_name' => $studentData['first_name'],
                'middle_name' => $studentData['middle_name'],
                'last_name' => $studentData['last_name'],
                'phone' => $studentData['phone'],
                'section_id' => $section->section_id,
                'specialization' => $studentData['specialization'],
                'is_submit' => false,
                'is_placed' => false,
                'is_active' => true,
            ]);

            $createdCount++;
        }

        $this->command->info("Extended Student seeder completed!");
        $this->command->info("Created: {$createdCount} additional students");
        $this->command->info("Skipped: {$skippedCount} students (already existed)");
    }
}
