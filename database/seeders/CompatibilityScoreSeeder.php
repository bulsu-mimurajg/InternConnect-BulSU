<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Internship;
use App\Services\MatchingService;

class CompatibilityScoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting compatibility score calculation and storage...');

        $matchingService = new MatchingService();
        
        // Get all students who have submitted assessments
        $students = Student::where('is_submit', true)
            ->where('is_active', true)
            ->get();

        $this->command->info("Found {$students->count()} students with submitted assessments.");

        $totalScores = 0;

        foreach ($students as $student) {
            $this->command->info("Processing student: {$student->first_name} {$student->last_name}");
            
            try {
                // Calculate and store all compatibility scores for this student
                $scores = $matchingService->calculateAndStoreCompatibilityScores($student);
                $totalScores += $scores->count();
                
                $this->command->info("  - Stored {$scores->count()} compatibility scores");
            } catch (\Exception $e) {
                $this->command->error("  - Error processing student {$student->id}: " . $e->getMessage());
            }
        }

        $this->command->info("Successfully stored {$totalScores} compatibility scores across all students!");
    }
}
