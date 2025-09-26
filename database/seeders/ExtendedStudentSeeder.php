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

        $additionalStudents = [
            [
                'student_number' => '2021-0004',
                'first_name' => 'Ana',
                'middle_name' => 'Reyes',
                'last_name' => 'Martinez',
                'phone' => '09123456792',
                'section_name' => 'BSIT-4A',
                'specialization' => 'Web Development',
                'birth_date' => '2000-11-25',
            ],
            [
                'student_number' => '2021-0005',
                'first_name' => 'Carlos',
                'middle_name' => 'Torres',
                'last_name' => 'Fernandez',
                'phone' => '09123456793',
                'section_name' => 'BSIT-4B',
                'specialization' => 'Mobile Development',
                'birth_date' => '2000-07-05',
            ],
            [
                'student_number' => '2021-0006',
                'first_name' => 'Isabella',
                'middle_name' => 'Cruz',
                'last_name' => 'Ramos',
                'phone' => '09123456794',
                'section_name' => 'BSIT-4C',
                'specialization' => 'Data Science',
                'birth_date' => '2000-09-12',
            ],
            [
                'student_number' => '2021-0007',
                'first_name' => 'Miguel',
                'middle_name' => 'Santos',
                'last_name' => 'Torres',
                'phone' => '09123456795',
                'section_name' => 'BSIT-4A',
                'specialization' => 'Cybersecurity',
                'birth_date' => '2000-12-03',
            ],
            [
                'student_number' => '2021-0008',
                'first_name' => 'Sofia',
                'middle_name' => 'Garcia',
                'last_name' => 'Lopez',
                'phone' => '09123456796',
                'section_name' => 'BSIT-4B',
                'specialization' => 'Artificial Intelligence',
                'birth_date' => '2000-04-18',
            ],
        ];

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
                'birth_date' => $studentData['birth_date'],
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
