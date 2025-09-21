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

        // Get BSIT-4A section (where emman is assigned and has students)
        $section = Section::where('section_name', 'BSIT-4A')->first();
        if (!$section) {
            $this->command->warn('BSIT-4A section not found. Please run SectionSeeder first.');
            return;
        }

        // Get students from the section
        $students = Student::whereHas('user.academeAccounts', function ($query) use ($section) {
            $query->where('section_id', $section->section_id);
        })->where('is_submit', true)->get();

        if ($students->isEmpty()) {
            $this->command->warn("No students found in {$section->section_name} section with submitted assessments.");
            return;
        }

        // Get active internships
        $internships = Internship::where('is_active', true)->get();
        if ($internships->isEmpty()) {
            $this->command->warn('No active internships found. Please run InternshipSeeder first.');
            return;
        }

        $this->command->info("Found {$students->count()} students in {$section->section_name} section");
        $this->command->info("Found {$internships->count()} active internships");

        // Create endorsements and placements
        $endorsementCount = 0;
        $placementCount = 0;

        foreach ($students as $index => $student) {
            $internship = $internships->get($index % $internships->count());
            
            // Create student match first (if not exists)
            $studentMatch = StudentMatch::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'internship_id' => $internship->id
                ],
                [
                    'rank' => $index + 1,
                    'compatibility_score' => rand(75, 95),
                    'endorsement_status' => 'pending',
                    'placement_status' => 'pending'
                ]
            );

            // Create endorsement for first 2 students
            if ($index < 2) {
                $endorsement = Endorsement::firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'internship_id' => $internship->id
                    ],
                    [
                        'status' => 'endorsed',
                        'compatibility_score' => $studentMatch->compatibility_score,
                        'notes' => "Excellent candidate for {$internship->position_title} position",
                        'endorsement_date' => now()->subDays(rand(1, 7))
                    ]
                );

                // Update student match endorsement status
                $studentMatch->update(['endorsement_status' => 'endorsed']);
                $endorsementCount++;

                $this->command->info("  ✓ Created endorsement for {$student->first_name} {$student->last_name} -> {$internship->hte->company_name}");
            }

            // Create placement for first student only
            if ($index === 0) {
                $placement = StudentPlacement::firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'internship_id' => $internship->id
                    ],
                    [
                        'status' => 'approved',
                        'compatibility_score' => $studentMatch->compatibility_score,
                        'placement_date' => now()->subDays(rand(1, 3))
                    ]
                );

                // Update student match placement status
                $studentMatch->update(['placement_status' => 'approved']);
                $placementCount++;

                $this->command->info("  ✓ Created placement for {$student->first_name} {$student->last_name} -> {$internship->hte->company_name}");
            }
        }

        $this->command->info("Successfully created {$endorsementCount} endorsements and {$placementCount} placements!");
        $this->command->info("These will now appear in the adviser reports for {$section->section_name} section.");
    }
}
