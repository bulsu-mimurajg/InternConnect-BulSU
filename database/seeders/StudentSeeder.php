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

        // Create 15 students with submitted assessments (for matched view)
        // and 2 students without submitted assessments
        $students = [
            // Students with submitted assessments (15 total)
            [
                'student_number' => '2022100101',
                'first_name' => 'John',
                'middle_name' => 'Michael',
                'last_name' => 'Smith',
                'phone' => '09123456789',
                'section_name' => 'BSIT-4A',
                'specialization' => 'WMAD',
                'is_submit' => true,
            ],
            [
                'student_number' => '2022100102',
                'first_name' => 'Sarah',
                'middle_name' => 'Jane',
                'last_name' => 'Johnson',
                'phone' => '09123456790',
                'section_name' => 'BSIT-4A',
                'specialization' => 'WMAD',
                'is_submit' => true,
            ],
            [
                'student_number' => '2022100103',
                'first_name' => 'Michael',
                'middle_name' => 'David',
                'last_name' => 'Brown',
                'phone' => '09123456791',
                'section_name' => 'BSIT-4A',
                'specialization' => 'WMAD',
                'is_submit' => true,
            ],
            [
                'student_number' => '2022100104',
                'first_name' => 'Emily',
                'middle_name' => 'Rose',
                'last_name' => 'Davis',
                'phone' => '09123456793',
                'section_name' => 'BSIT-4B',
                'specialization' => 'WMAD',
                'is_submit' => true,
            ],
            [
                'student_number' => '2022100105',
                'first_name' => 'David',
                'middle_name' => 'James',
                'last_name' => 'Wilson',
                'phone' => '09123456794',
                'section_name' => 'BSIT-4B',
                'specialization' => 'WMAD',
                'is_submit' => true,
            ],
            [
                'student_number' => '2022100106',
                'first_name' => 'Jessica',
                'middle_name' => 'Ann',
                'last_name' => 'Miller',
                'phone' => '09123456795',
                'section_name' => 'BSIT-4B',
                'specialization' => 'WMAD',
                'is_submit' => true,
            ],
            [
                'student_number' => '2022100107',
                'first_name' => 'Christopher',
                'middle_name' => 'Lee',
                'last_name' => 'Garcia',
                'phone' => '09123456796',
                'section_name' => 'BSIT-4B',
                'specialization' => 'WMAD',
                'is_submit' => true,
            ],
            [
                'student_number' => '2022100108',
                'first_name' => 'Ashley',
                'middle_name' => 'Marie',
                'last_name' => 'Martinez',
                'phone' => '09123456797',
                'section_name' => 'BSIT-4C',
                'specialization' => 'WMAD',
                'is_submit' => true,
            ],
            [
                'student_number' => '2022100109',
                'first_name' => 'Matthew',
                'middle_name' => 'Ryan',
                'last_name' => 'Anderson',
                'phone' => '09123456798',
                'section_name' => 'BSIT-4C',
                'specialization' => 'WMAD',
                'is_submit' => true,
            ],
            [
                'student_number' => '2022100110',
                'first_name' => 'Amanda',
                'middle_name' => 'Grace',
                'last_name' => 'Taylor',
                'phone' => '09123456799',
                'section_name' => 'BSIT-4C',
                'specialization' => 'WMAD',
                'is_submit' => true,
            ],
            [
                'student_number' => '2022100111',
                'first_name' => 'Daniel',
                'middle_name' => 'Paul',
                'last_name' => 'Thomas',
                'phone' => '09123456800',
                'section_name' => 'BSIT-4A',
                'specialization' => 'WMAD',
                'is_submit' => true,
            ],
            [
                'student_number' => '2022100112',
                'first_name' => 'Jennifer',
                'middle_name' => 'Lynn',
                'last_name' => 'Jackson',
                'phone' => '09123456801',
                'section_name' => 'BSIT-4A',
                'specialization' => 'WMAD',
                'is_submit' => true,
            ],
            [
                'student_number' => '2022100113',
                'first_name' => 'Robert',
                'middle_name' => 'Scott',
                'last_name' => 'White',
                'phone' => '09123456802',
                'section_name' => 'BSIT-4B',
                'specialization' => 'WMAD',
                'is_submit' => true,
            ],
            [
                'student_number' => '2022100114',
                'first_name' => 'Lisa',
                'middle_name' => 'Beth',
                'last_name' => 'Harris',
                'phone' => '09123456803',
                'section_name' => 'BSIT-4C',
                'specialization' => 'WMAD',
                'is_submit' => true,
            ],
            [
                'student_number' => '2022100115',
                'first_name' => 'Kevin',
                'middle_name' => 'John',
                'last_name' => 'Martin',
                'phone' => '09123456804',
                'section_name' => 'BSIT-4C',
                'specialization' => 'WMAD',
                'is_submit' => true,
            ],
            // Students without submitted assessments (2 total)
            [
                'student_number' => '2022100116',
                'first_name' => 'Alex',
                'middle_name' => 'Jordan',
                'last_name' => 'Thompson',
                'phone' => '09123456805',
                'section_name' => 'BSIT-4A',
                'specialization' => 'WMAD',
                'is_submit' => false,
            ],
            [
                'student_number' => '2022100117',
                'first_name' => 'Nicole',
                'middle_name' => 'Kay',
                'last_name' => 'Garcia',
                'phone' => '09123456806',
                'section_name' => 'BSIT-4B',
                'specialization' => 'WMAD',
                'is_submit' => false,
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
            $username = isset($studentData['username']) ? $studentData['username'] : strtolower(str_replace(' ', '.', $studentData['first_name'] . ' ' . $studentData['last_name']));
            $password = isset($studentData['password']) ? $studentData['password'] : 'password123';
            
            $user = User::create([
                'username' => $username,
                'email' => strtolower(str_replace(' ', '.', $studentData['first_name'] . ' ' . $studentData['last_name'])) . '@example.com',
                'password' => Hash::make($password),
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
                'is_submit' => $studentData['is_submit'],
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
