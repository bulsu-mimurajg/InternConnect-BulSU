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
        $this->command->info('Creating 5 test students with specific statuses for different views...');

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

        // Get sections
        $sections = Section::all();
        if ($sections->isEmpty()) {
            $this->command->warn('No sections found. Please run SectionSeeder first.');
            return;
        }

        // Get all subcategories for assessment scores
        $subCategories = \App\Models\SubCategory::all();
        if ($subCategories->isEmpty()) {
            $this->command->warn('No subcategories found. Please run CategorySeeder first.');
            return;
        }

        $this->command->info("Found {$mariaInternships->count()} active internships for MariaTech Solutions");

        // Create exactly 5 students with specific statuses
        $testStudents = [
            // Student 1: Basic student for list.tsx (no special status)
            [
                'student_number' => '2022100201',
                'first_name' => 'Alex',
                'last_name' => 'Johnson',
                'middle_name' => 'A',
                'specialization' => 'BA',
                'status' => 'basic', // Just a regular student
            ],
            // Student 2: Matched student for matched.tsx (has match, not endorsed)
            [
                'student_number' => '2022100202',
                'first_name' => 'Bob',
                'last_name' => 'Smith',
                'middle_name' => 'B',
                'specialization' => 'WMAD',
                'status' => 'matched', // Has match but not endorsed
            ],
            // Student 3: Matched student for matched.tsx (has match, not endorsed)
            [
                'student_number' => '2022100203',
                'first_name' => 'Carol',
                'last_name' => 'Davis',
                'middle_name' => 'C',
                'specialization' => 'SM',
                'status' => 'matched', // Has match but not endorsed
            ],
            // Student 4: Endorsed student for endorsed.tsx (endorsed, pending HTE approval)
            [
                'student_number' => '2022100204',
                'first_name' => 'David',
                'last_name' => 'Martinez',
                'middle_name' => 'D',
                'specialization' => 'WMAD',
                'status' => 'endorsed', // Endorsed but pending HTE approval
            ],
            // Student 5: Placed student for placed.tsx (approved placement)
            [
                'student_number' => '2022100205',
                'first_name' => 'Eva',
                'last_name' => 'Brown',
                'middle_name' => 'E',
                'specialization' => 'BA',
                'status' => 'placed', // Already placed and approved
            ],
        ];

        $createdStudents = [];
        $endorsementCount = 0;
        $placementCount = 0;

        foreach ($testStudents as $index => $studentData) {
            // Create user account
            $user = \App\Models\User::create([
                'username' => strtolower($studentData['first_name'] . '.' . $studentData['last_name']),
                'email' => strtolower($studentData['first_name'] . '.' . $studentData['last_name']) . '@example.com',
                'password' => bcrypt('password'),
                'status' => 'verified',
                'email_verified_at' => now(),
            ]);
            
            // Assign student role
            $user->assignRole('student');
            
            // Create student record
            $student = Student::create([
                'user_id' => $user->id,
                'student_number' => $studentData['student_number'],
                'first_name' => $studentData['first_name'],
                'last_name' => $studentData['last_name'],
                'middle_name' => $studentData['middle_name'],
                'phone' => '0912345678' . ($index + 1),
                'specialization' => $studentData['specialization'],
                'section_id' => $sections->random()->section_id,
                'is_submit' => true,
                'is_placed' => $studentData['status'] === 'placed',
                'is_active' => true,
            ]);

            // Create academe account
            \App\Models\AcademeAccount::create([
                'user_id' => $user->id,
                'section_id' => $student->section_id,
            ]);

            // Create assessment scores for all students (1-5 scale)
            foreach ($subCategories as $subCategory) {
                // Generate score between 1-5 with decimal precision
                $score = round(mt_rand(100, 500) / 100, 2);
                \App\Models\StudentScore::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'sub_category_id' => $subCategory->id,
                    ],
                    [
                        'score' => $score,
                    ]
                );
            }

            $createdStudents[] = $student;
            $this->command->info("  ✓ Created student: {$student->first_name} {$student->last_name} ({$studentData['status']})");
        }

        // Use Maria's first internship for all matches
        $mariaInternship = $mariaInternships->first();

        // Create matches and endorsements/placements based on status
        foreach ($createdStudents as $index => $student) {
            $studentData = $testStudents[$index];
            
            if (in_array($studentData['status'], ['matched', 'endorsed', 'placed'])) {
                // Create student match with Maria's internship
                $studentMatch = StudentMatch::create([
                    'student_id' => $student->id,
                    'internship_id' => $mariaInternship->id,
                    'rank' => $index + 1,
                    'compatibility_score' => rand(80, 95),
                    'endorsement_status' => in_array($studentData['status'], ['endorsed', 'placed']) ? 'endorsed' : 'pending',
                    'placement_status' => $studentData['status'] === 'placed' ? 'approved' : 'pending'
                ]);

                if (in_array($studentData['status'], ['endorsed', 'placed'])) {
                    // Create endorsement with Maria's internship
                    Endorsement::create([
                        'student_id' => $student->id,
                        'internship_id' => $mariaInternship->id,
                        'status' => 'endorsed',
                        'compatibility_score' => $studentMatch->compatibility_score,
                        'notes' => "Excellent candidate for {$mariaInternship->position_title} position at MariaTech Solutions",
                        'endorsement_date' => now()->subDays(rand(1, 5))
                    ]);
                    $endorsementCount++;
                }

                if ($studentData['status'] === 'placed') {
                    // Create placement with Maria's internship
                    StudentPlacement::create([
                        'student_id' => $student->id,
                        'internship_id' => $mariaInternship->id,
                        'status' => 'approved',
                        'compatibility_score' => $studentMatch->compatibility_score,
                        'placement_date' => now()->subDays(rand(1, 3))
                    ]);
                    $placementCount++;
                }
            }
        }

        $this->command->info("Successfully created 5 test students for MariaTech Solutions:");
        $this->command->info("  • 1 basic student (for list.tsx)");
        $this->command->info("  • 2 matched students (for matched.tsx)");
        $this->command->info("  • 1 endorsed student (for endorsed.tsx)");
        $this->command->info("  • 1 placed student (for placed.tsx)");
        $this->command->info("All students are associated with Maria's HTE (MariaTech Solutions)");
        $this->command->info("Created {$endorsementCount} endorsements and {$placementCount} placements!");
    }
}
