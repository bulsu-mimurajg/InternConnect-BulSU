<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Internship;
use App\Models\Endorsement;
use App\Models\StudentPlacement;
use App\Models\StudentMatch;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Seeder;

class EndorsementPlacementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating endorsements and placements for testing...');

        // Get Maria's HTE and her internships specifically
        $mariaUser = User::where('username', 'maria')->first();
        if (!$mariaUser || !$mariaUser->hte) {
            $this->command->warn('Maria user or HTE not found. Please run HTESeeder first.');
            return;
        }

        $mariaHTE = $mariaUser->hte;
        $mariaInternships = $mariaHTE->internships()->where('is_active', true)->get();
        
        if ($mariaInternships->isEmpty()) {
            $this->command->warn('No active internships found for MariaTech Solutions. Please run InternshipSeeder first.');
            return;
        }

        // Get all sections to ensure we have enough students for at least 3 per internship
        $sections = Section::all();
        if ($sections->isEmpty()) {
            $this->command->warn('No sections found. Please run SectionSeeder first.');
            return;
        }

        // Get students from all sections (try both is_submit true and students with scores)
        $students = Student::whereHas('user.academeAccounts', function ($query) use ($sections) {
            $query->whereIn('section_id', $sections->pluck('section_id'));
        })->where(function ($query) {
            $query->where('is_submit', true)
                  ->orWhereHas('scores'); // Include students who have assessment scores
        })->get();

        if ($students->isEmpty()) {
            $this->command->warn("No students found in any sections with submitted assessments.");
            return;
        }

        $this->command->info("Found {$students->count()} students across " . $sections->count() . " sections");
        $this->command->info("Found {$mariaInternships->count()} active internships for MariaTech Solutions");

        // If we don't have enough students for 3 per internship, create additional students
        $studentsNeeded = $mariaInternships->count() * 3;
        if ($students->count() < $studentsNeeded) {
            $this->command->info("Need {$studentsNeeded} students but only have {$students->count()}. Creating additional students...");
            
            // Create additional students
            $additionalStudentsNeeded = $studentsNeeded - $students->count();
            $additionalStudents = [];
            
            for ($i = 0; $i < $additionalStudentsNeeded; $i++) {
                $studentNumber = $i + 1;
                $timestamp = time();
                $uniqueId = $timestamp + $i;
                
                // Generate student number in format '2022100000' (year + sequential number)
                $studentNumberFormatted = '2022' . str_pad($studentNumber, 6, '0', STR_PAD_LEFT);
                
                $user = \App\Models\User::create([
                    'username' => "student{$studentNumberFormatted}",
                    'email' => "student{$studentNumberFormatted}@example.com",
                    'password' => bcrypt('password'),
                    'status' => 'verified',
                    'email_verified_at' => now(),
                ]);
                
                // Assign student role using Spatie permissions
                $user->assignRole('student');
                
                $student = Student::create([
                    'user_id' => $user->id,
                    'student_number' => $studentNumberFormatted,
                    'first_name' => "Student{$studentNumber}",
                    'last_name' => "LastName{$studentNumber}",
                    'middle_name' => "Middle{$studentNumber}",
                    'phone' => "0912345678{$studentNumber}",
                    'specialization' => "Computer Science",
                    'address' => "Sample Address {$studentNumber}",
                    'birth_date' => now()->subYears(20)->format('Y-m-d'),
                    'section_id' => $sections->random()->section_id,
                    'is_submit' => true,
                ]);
                
                // Create some sample assessment scores for the student
                $subCategories = \App\Models\SubCategory::take(5)->get();
                foreach ($subCategories as $subCategory) {
                    \App\Models\StudentScore::create([
                        'student_id' => $student->id,
                        'sub_category_id' => $subCategory->id,
                        'score' => rand(70, 95),
                    ]);
                }
                
                $additionalStudents[] = $student;
                $this->command->info("  ✓ Created additional student: {$student->first_name} {$student->last_name}");
            }
            
            // Merge additional students with existing ones
            $students = $students->merge(collect($additionalStudents));
            $this->command->info("Now have {$students->count()} students total.");
        }

        // Create endorsements and placements
        $endorsementCount = 0;
        $placementCount = 0;

        // Ensure at least 3 endorsed students per internship (not placed)
        $studentsPerInternship = 3;
        $totalEndorsementsNeeded = $mariaInternships->count() * $studentsPerInternship;
        
        // If we don't have enough students, use what we have but ensure at least 1 per internship
        if ($students->count() < $totalEndorsementsNeeded) {
            $studentsPerInternship = max(1, intval($students->count() / $mariaInternships->count()));
            $this->command->info("Note: Limited students available. Creating {$studentsPerInternship} endorsements per internship.");
        }

        $studentIndex = 0;
        
        // Create endorsements for each internship
        foreach ($mariaInternships as $internshipIndex => $internship) {
            $this->command->info("Processing internship: {$internship->position_title}");
            
            for ($i = 0; $i < $studentsPerInternship && $studentIndex < $students->count(); $i++) {
                $student = $students[$studentIndex];
                $studentIndex++;
                
                // Create student match first (if not exists)
                $studentMatch = StudentMatch::firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'internship_id' => $internship->id
                    ],
                    [
                        'rank' => ($internshipIndex * $studentsPerInternship) + $i + 1,
                        'compatibility_score' => rand(70, 95),
                        'endorsement_status' => 'pending',
                        'placement_status' => 'pending'
                    ]
                );

                // Create endorsement (these will be NOT placed, so HTE can review them)
                $endorsement = Endorsement::firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'internship_id' => $internship->id
                    ],
                    [
                        'status' => 'endorsed',
                        'compatibility_score' => $studentMatch->compatibility_score,
                        'notes' => "Excellent candidate for {$internship->position_title} position",
                        'endorsement_date' => now()->subDays(rand(1, 10))
                    ]
                );

                // Update student match endorsement status
                $studentMatch->update(['endorsement_status' => 'endorsed']);
                $endorsementCount++;

                $statusText = $endorsement->wasRecentlyCreated ? 'endorsement' : 'existing endorsement';
                $this->command->info("  ✓ Created {$statusText} for {$student->first_name} {$student->last_name} -> {$internship->hte->company_name} ({$internship->position_title})");
            }
        }

        // Create a few placements to show some students are already placed (separate from endorsements)
        $placementStudents = $students->slice(0, min(2, $students->count()));
        foreach ($placementStudents as $index => $student) {
            $internship = $mariaInternships->get($index % $mariaInternships->count());
            
            // Create student match for placement
            $studentMatch = StudentMatch::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'internship_id' => $internship->id
                ],
                [
                    'rank' => 999 + $index, // High rank to separate from endorsements
                    'compatibility_score' => rand(70, 95),
                    'endorsement_status' => 'endorsed',
                    'placement_status' => 'pending'
                ]
            );

            // Create placement (this student is already placed)
            $placement = StudentPlacement::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'internship_id' => $internship->id
                ],
                [
                    'status' => 'approved',
                    'compatibility_score' => $studentMatch->compatibility_score,
                    'placement_date' => now()->subDays(rand(1, 5))
                ]
            );

            // Update student match placement status
            $studentMatch->update(['placement_status' => 'approved']);
            $placementCount++;

            $this->command->info("  ✓ Created placement for {$student->first_name} {$student->last_name} -> {$internship->hte->company_name}");
        }

        $this->command->info("Successfully created {$endorsementCount} endorsements and {$placementCount} placements!");
        $this->command->info("These will now appear in the HTE endorsement table for MariaTech Solutions.");
    }
}
