<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Deadline;
use App\Models\InternshipSeason;
use Carbon\Carbon;

class DeadlineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, ensure we have an active season
        $activeSeason = InternshipSeason::where('status', 'active')->first();
        
        if (!$activeSeason) {
            $this->command->warn('No active internship season found. Creating a default season first...');
            
            $activeSeason = InternshipSeason::create([
                'name' => 'AY 2024-2025 First Semester (Default)',
                'start_date' => Carbon::now()->subMonths(3),
                'end_date' => Carbon::now()->addMonths(3),
                'status' => 'active',
            ]);
            
            $this->command->info("Created default active season: {$activeSeason->name}");
        }

        $categories = [
            'student_verification',
            'student_assessment_form', 
            'hte_assessment_form',
            'internship_placement',
            'archive_students'  // Added the new archive_students category
        ];

        $now = Carbon::now();
        
        foreach ($categories as $category) {
            // Create a 1-day duration deadline starting from now
            $startDate = $now->copy();
            $endDate = $now->copy()->addDay(); // 1 day duration
            
            // Check if deadline already exists for this category in the active season
            $existingDeadline = Deadline::where('category', $category)
                ->where('internship_season_id', $activeSeason->id)
                ->first();
            
            if (!$existingDeadline) {
                Deadline::create([
                    'title' => $this->getTitleForCategory($category),
                    'category' => $category,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'active',
                    'internship_season_id' => $activeSeason->id,  // Associate with active season
                ]);
                
                $this->command->info("Created deadline for category: {$category} in season: {$activeSeason->name}");
            } else {
                $this->command->warn("Deadline already exists for category: {$category} in season: {$activeSeason->name}");
            }
        }
    }

    /**
     * Get appropriate title for each category
     */
    private function getTitleForCategory(string $category): string
    {
        return match($category) {
            'student_verification' => 'Student Verification Deadline',
            'student_assessment_form' => 'Student Assessment Form Submission Deadline',
            'hte_assessment_form' => 'HTE Assessment Form Submission Deadline',
            'internship_placement' => 'Internship Placement Deadline',
            'archive_students' => 'Archive Students Deadline',
            default => ucwords(str_replace('_', ' ', $category)) . ' Deadline',
        };
    }

    /**
     * Create deadlines with custom duration
     * Usage: php artisan db:seed --class=DeadlineSeeder --duration=7 (for 7 days)
     */
    public function runWithCustomDuration(int $durationDays = 1): void
    {
        // First, ensure we have an active season
        $activeSeason = InternshipSeason::where('status', 'active')->first();
        
        if (!$activeSeason) {
            $this->command->warn('No active internship season found. Creating a default season first...');
            
            $activeSeason = InternshipSeason::create([
                'name' => 'AY 2024-2025 First Semester (Default)',
                'start_date' => Carbon::now()->subMonths(3),
                'end_date' => Carbon::now()->addMonths(3),
                'status' => 'active',
            ]);
            
            $this->command->info("Created default active season: {$activeSeason->name}");
        }

        $categories = [
            'student_verification',
            'student_assessment_form', 
            'hte_assessment_form',
            'internship_placement',
            'archive_students'  // Added the new archive_students category
        ];

        $now = Carbon::now();
        
        foreach ($categories as $category) {
            $startDate = $now->copy();
            $endDate = $now->copy()->addDays($durationDays);
            
            // Check if deadline already exists for this category in the active season
            $existingDeadline = Deadline::where('category', $category)
                ->where('internship_season_id', $activeSeason->id)
                ->first();
            
            if (!$existingDeadline) {
                Deadline::create([
                    'title' => $this->getTitleForCategory($category),
                    'category' => $category,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'active',
                    'internship_season_id' => $activeSeason->id,  // Associate with active season
                ]);
                
                $this->command->info("Created {$durationDays}-day deadline for category: {$category} in season: {$activeSeason->name}");
            } else {
                $this->command->warn("Deadline already exists for category: {$category} in season: {$activeSeason->name}");
            }
        }
    }

    /**
     * Create deadlines for a specific season
     * Usage: php artisan db:seed --class=DeadlineSeeder --season-id=1
     */
    public function runForSeason(int $seasonId): void
    {
        $season = InternshipSeason::find($seasonId);
        
        if (!$season) {
            $this->command->error("Season with ID {$seasonId} not found.");
            return;
        }

        $categories = [
            'student_verification',
            'student_assessment_form', 
            'hte_assessment_form',
            'internship_placement',
            'archive_students'
        ];

        $now = Carbon::now();
        
        foreach ($categories as $category) {
            $startDate = $now->copy();
            $endDate = $now->copy()->addDay();
            
            // Check if deadline already exists for this category in the specified season
            $existingDeadline = Deadline::where('category', $category)
                ->where('internship_season_id', $season->id)
                ->first();
            
            if (!$existingDeadline) {
                Deadline::create([
                    'title' => $this->getTitleForCategory($category),
                    'category' => $category,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'active',
                    'internship_season_id' => $season->id,
                ]);
                
                $this->command->info("Created deadline for category: {$category} in season: {$season->name}");
            } else {
                $this->command->warn("Deadline already exists for category: {$category} in season: {$season->name}");
            }
        }
    }
}
