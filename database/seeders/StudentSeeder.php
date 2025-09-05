<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\User;
use App\Models\Section;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class StudentSeeder extends Seeder
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

        $students = [
            [
                'student_number' => '2021-0001',
                'first_name' => 'Juan',
                'middle_name' => 'Santos',
                'last_name' => 'Dela Cruz',
                'phone' => '09123456789',
                'section_name' => 'BSIT-4A',
                'specialization' => 'Programming',
                'address' => '123 Main St, Quezon City',
                'birth_date' => '2000-01-15',
            ],
            [
                'student_number' => '2021-0002',
                'first_name' => 'Maria',
                'middle_name' => 'Garcia',
                'last_name' => 'Santos',
                'phone' => '09123456790',
                'section_name' => 'BSIT-4B',
                'specialization' => 'Networking',
                'address' => '456 Oak Ave, Manila',
                'birth_date' => '2000-03-20',
            ],
            [
                'student_number' => '2021-0003',
                'first_name' => 'Pedro',
                'middle_name' => 'Lopez',
                'last_name' => 'Gonzales',
                'phone' => '09123456791',
                'section_name' => 'BSIT-4C',
                'specialization' => 'Database',
                'address' => '789 Pine Rd, Makati',
                'birth_date' => '2000-05-10',
            ],
        ];

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($students as $studentData) {
            // Check if student already exists
            $existingStudent = Student::where('student_number', $studentData['student_number'])->first();
            if ($existingStudent) {
                $this->command->info("Student {$studentData['student_number']} already exists. Skipping.");
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
                'address' => $studentData['address'],
                'birth_date' => $studentData['birth_date'],
                'is_submit' => false,
                'is_placed' => false,
                'is_active' => true,
            ]);

            $createdCount++;
        }

        $this->command->info("Student seeder completed!");
        $this->command->info("Created: {$createdCount} students");
        $this->command->info("Skipped: {$skippedCount} students (already existed)");

        // Display section distribution
        $this->command->info("\nSection Distribution:");
        foreach ($availableSections as $section) {
            $studentCount = Student::where('section_id', $section->section_id)->count();
            $this->command->info("Section {$section->section_name}: {$studentCount} students");
        }
    }
}
