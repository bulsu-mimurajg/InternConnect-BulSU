<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Internship;
use App\Models\StudentMatch;
use App\Models\StudentScore;
use App\Models\SubcategoryWeight;
use Illuminate\Database\Seeder;
use App\Services\MatchingService;

class StudentMatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all students who have submitted assessments
        $students = Student::where('is_submit', true)->get();
        
        // Get all active internships
        $internships = Internship::where('is_active', true)->get();
        
        if ($students->isEmpty() || $internships->isEmpty()) {
            $this->command->info('No students with assessments or active internships found. Skipping StudentMatchSeeder.');
            return;
        }

        // Clear existing matches to prevent duplicates
        $this->command->info('Clearing existing student matches...');
        StudentMatch::truncate();

        $this->command->info('Creating student-internship matches...');
        
        $matchingService = new MatchingService();
        $createdMatches = 0;

        foreach ($students as $student) {
            // Calculate and store all compatibility scores for this student
            $scores = $matchingService->calculateAndStoreCompatibilityScores($student);
            
            $createdMatches += $scores->count();
            $this->command->info("  - Stored {$scores->count()} compatibility scores for {$student->first_name} {$student->last_name}");
        }

        $this->command->info("Successfully stored {$createdMatches} compatibility scores across all students!");
        $this->command->info("All compatibility scores have been stored in the student_matches table for dynamic sorting.");
    }
}
