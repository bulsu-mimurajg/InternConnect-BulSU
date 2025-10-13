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
        // First, check if we have any seasons
        $seasons = InternshipSeason::all();
        
        if ($seasons->isEmpty()) {
            $this->command->warn('No internship seasons found. Creating a default season first...');
            
            $defaultSeason = InternshipSeason::create([
                'name' => 'AY 2024-2025 First Semester (Default)',
                'start_date' => Carbon::now()->subMonths(3),
                'end_date' => Carbon::now()->addMonths(3),
                'status' => 'inactive', // Start as inactive
            ]);
            
            $this->command->info("Created default season: {$defaultSeason->name}");
            $seasons = collect([$defaultSeason]);
        }

        // Use the first season if no active season exists
        $targetSeason = InternshipSeason::where('status', 'active')->first() ?? $seasons->first();
        
        $this->command->info("Creating deadlines for season: {$targetSeason->name} (Status: {$targetSeason->status})");

        $categories = [
            'hte_assessment_form',        // 1st - HTE Assessment
            'student_verification',       // 2nd - Student Verification  
            'student_assessment_form',    // 3rd - Student Assessment
            'internship_placement',       // 4th - Internship Placement
            'archive_students'            // 5th - Archive Students
        ];

        $now = Carbon::now();
        $createdCount = 0;
        
        foreach ($categories as $index => $category) {
            // Create deadlines with staggered start times (1 day apart)
            $startDate = $now->copy()->addDays($index);
            $endDate = $startDate->copy()->addDays(7); // 7-day duration for each deadline
            
            // Check if deadline already exists for this category in the target season
            $existingDeadline = Deadline::where('category', $category)
                ->where('internship_season_id', $targetSeason->id)
                ->first();
            
            if (!$existingDeadline) {
                Deadline::create([
                    'title' => $this->getTitleForCategory($category),
                    'category' => $category,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'active',
                    'internship_season_id' => $targetSeason->id,
                ]);
                
                $this->command->info("Created deadline for category: {$category} in season: {$targetSeason->name} (Start: {$startDate->format('M d, Y')})");
                $createdCount++;
            } else {
                $this->command->warn("Deadline already exists for category: {$category} in season: {$targetSeason->name}");
            }
        }
        
        $this->command->info("Deadline seeding completed. Created {$createdCount} new deadlines.");
        
        // Show season activation status
        if ($targetSeason->status === 'inactive') {
            $this->command->info("Note: Season '{$targetSeason->name}' is inactive. You can activate it after all 5 deadlines are created.");
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
        // First, check if we have any seasons
        $seasons = InternshipSeason::all();
        
        if ($seasons->isEmpty()) {
            $this->command->warn('No internship seasons found. Creating a default season first...');
            
            $defaultSeason = InternshipSeason::create([
                'name' => 'AY 2024-2025 First Semester (Default)',
                'start_date' => Carbon::now()->subMonths(3),
                'end_date' => Carbon::now()->addMonths(3),
                'status' => 'inactive',
            ]);
            
            $this->command->info("Created default season: {$defaultSeason->name}");
            $seasons = collect([$defaultSeason]);
        }

        // Use the first season if no active season exists
        $targetSeason = InternshipSeason::where('status', 'active')->first() ?? $seasons->first();
        
        $this->command->info("Creating {$durationDays}-day deadlines for season: {$targetSeason->name}");

        $categories = [
            'hte_assessment_form',        // 1st - HTE Assessment
            'student_verification',       // 2nd - Student Verification  
            'student_assessment_form',    // 3rd - Student Assessment
            'internship_placement',       // 4th - Internship Placement
            'archive_students'            // 5th - Archive Students
        ];

        $now = Carbon::now();
        $createdCount = 0;
        
        foreach ($categories as $index => $category) {
            // Create deadlines with staggered start times (1 day apart)
            $startDate = $now->copy()->addDays($index);
            $endDate = $startDate->copy()->addDays($durationDays);
            
            // Check if deadline already exists for this category in the target season
            $existingDeadline = Deadline::where('category', $category)
                ->where('internship_season_id', $targetSeason->id)
                ->first();
            
            if (!$existingDeadline) {
                Deadline::create([
                    'title' => $this->getTitleForCategory($category),
                    'category' => $category,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'active',
                    'internship_season_id' => $targetSeason->id,
                ]);
                
                $this->command->info("Created {$durationDays}-day deadline for category: {$category} in season: {$targetSeason->name}");
                $createdCount++;
            } else {
                $this->command->warn("Deadline already exists for category: {$category} in season: {$targetSeason->name}");
            }
        }
        
        $this->command->info("Deadline seeding completed. Created {$createdCount} new deadlines.");
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

        $this->command->info("Creating deadlines for season: {$season->name} (Status: {$season->status})");

        $categories = [
            'hte_assessment_form',        // 1st - HTE Assessment
            'student_verification',       // 2nd - Student Verification  
            'student_assessment_form',    // 3rd - Student Assessment
            'internship_placement',       // 4th - Internship Placement
            'archive_students'            // 5th - Archive Students
        ];

        $now = Carbon::now();
        $createdCount = 0;
        
        foreach ($categories as $index => $category) {
            // Create deadlines with staggered start times (1 day apart)
            $startDate = $now->copy()->addDays($index);
            $endDate = $startDate->copy()->addDays(7); // 7-day duration for each deadline
            
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
                
                $this->command->info("Created deadline for category: {$category} in season: {$season->name} (Start: {$startDate->format('M d, Y')})");
                $createdCount++;
            } else {
                $this->command->warn("Deadline already exists for category: {$category} in season: {$season->name}");
            }
        }
        
        $this->command->info("Deadline seeding completed. Created {$createdCount} new deadlines.");
        
        // Show season activation status
        if ($season->status === 'inactive') {
            $this->command->info("Note: Season '{$season->name}' is inactive. You can activate it after all 5 deadlines are created.");
        }
    }
}
