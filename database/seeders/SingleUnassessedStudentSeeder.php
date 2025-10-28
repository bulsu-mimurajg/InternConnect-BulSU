<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\User;
use App\Models\Section;
use App\Models\InternshipSeason;
use App\Models\HTE;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SingleUnassessedStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure student role exists
        $studentRole = Role::where('name', 'student')->first();
        if (!$studentRole) {
            $this->command->warn('Student role not found. Please run RolePermissionSeeder first.');
            return;
        }

        // Ensure HTE role exists
        $hteRole = Role::where('name', 'hte')->first();
        if (!$hteRole) {
            $this->command->warn('HTE role not found. Please run RolePermissionSeeder first.');
            return;
        }

        // Ensure at least one section exists; create a default if none
        $section = Section::first();
        if (!$section) {
            $section = Section::create([
                'section_name' => 'BSIT-DEFAULT',
                'section_id' => 'BSIT-DEFAULT',
            ]);
            $this->command->info('Created default section: BSIT-DEFAULT');
        }

        // Ensure an internship season exists; create default if none
        $season = InternshipSeason::first();
        if (!$season) {
            $season = InternshipSeason::create([
                'name' => 'Default Season (SingleUnassessedStudentSeeder)',
                'start_date' => now()->subMonths(1),
                'end_date' => now()->addMonths(6),
                'status' => 'inactive',
            ]);
            $this->command->info('Created default internship season for seeder.');
        }

        // === DELETE OLD RECORDS FIRST ===
        $studentNumber = '2025100001';
        $studentUsername = 'student';
        $hteUsername = 'hte';

        // Delete old student records
        $oldStudentUser = User::where('username', $studentUsername)->first();
        if ($oldStudentUser) {
            $oldStudent = Student::where('user_id', $oldStudentUser->id)->first();
            if ($oldStudent) {
                $oldStudent->delete();
                $this->command->info("Deleted old student record: {$oldStudent->student_number}");
            }
            $oldStudentUser->delete();
            $this->command->info("Deleted old student user: {$studentUsername}");
        }

        // Also delete by student number
        $oldStudentByNumber = Student::where('student_number', $studentNumber)->first();
        if ($oldStudentByNumber) {
            $oldStudentByNumber->delete();
            $this->command->info("Deleted old student by number: {$studentNumber}");
        }

        // Delete old HTE records
        $oldHteUser = User::where('username', $hteUsername)->first();
        if ($oldHteUser) {
            $oldHte = HTE::where('user_id', $oldHteUser->id)->first();
            if ($oldHte) {
                $oldHte->delete();
                $this->command->info("Deleted old HTE record: {$oldHte->company_name}");
            }
            $oldHteUser->delete();
            $this->command->info("Deleted old HTE user: {$hteUsername}");
        }

        // === CREATE UNASSESSED STUDENT ===
        // Create user for the student
        $studentUser = User::create([
            'username' => $studentUsername,
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'status' => 'verified',
        ]);

        // Assign student role
        $studentUser->assignRole('student');

        // Create the student record with is_submit = false (no assessment submitted)
        $student = Student::create([
            'user_id' => $studentUser->id,
            'student_number' => $studentNumber,
            'first_name' => 'Unassessed',
            'middle_name' => 'Test',
            'last_name' => 'Student',
            'phone' => '09000000000',
            'section_id' => $section->section_id,
            'specialization' => 'WMAD',
            'is_submit' => false,
            'is_placed' => false,
            'is_active' => true,
            'internship_season_id' => $season->id,
        ]);

        $this->command->info("Created unassessed student: {$student->student_number} (username: {$studentUsername})");

        // === CREATE UNASSESSED HTE ===
        // Create user for the HTE
        $hteUser = User::create([
            'username' => $hteUsername,
            'email' => 'hte@example.com',
            'password' => Hash::make('password'),
            'status' => 'verified',
        ]);

        // Assign HTE role
        $hteUser->assignRole('hte');

        // Create the HTE record with is_submit = false (no assessment submitted)
        $hte = HTE::create([
            'user_id' => $hteUser->id,
            'company_name' => 'Unassessed Company Inc.',
            'company_address' => '123 Test Street, Sample City, SC 12345',
            'company_email' => 'contact@unassessed-company.com',
            'cperson_fname' => 'Test',
            'cperson_lname' => 'Contact',
            'cperson_position' => 'HR Manager',
            'cperson_contactnum' => '+63-900-000-0000',
            'is_active' => true,
            'is_submit' => false, // No assessment submitted
        ]);

        $this->command->info("Created unassessed HTE: {$hte->company_name} (username: {$hteUsername})");

        $this->command->info("SingleUnassessedStudentSeeder completed!");
    }
}

