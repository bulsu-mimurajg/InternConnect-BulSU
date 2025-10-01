<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Deadline;
use Carbon\Carbon;

class DeadlineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'student_verification',
            'student_assessment_form', 
            'hte_assessment_form',
            'internship_placement'
        ];

        $now = Carbon::now();
        
        foreach ($categories as $category) {
            // Create a 1-day duration deadline starting from now
            $startDate = $now->copy();
            $endDate = $now->copy()->addDay(); // 1 day duration
            
            // Check if deadline already exists for this category
            $existingDeadline = Deadline::where('category', $category)->first();
            
            if (!$existingDeadline) {
                Deadline::create([
                    'title' => $this->getTitleForCategory($category),
                    'category' => $category,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'active',
                ]);
                
                $this->command->info("Created deadline for category: {$category}");
            } else {
                $this->command->warn("Deadline already exists for category: {$category}");
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
            default => ucwords(str_replace('_', ' ', $category)) . ' Deadline',
        };
    }

    /**
     * Create deadlines with custom duration
     * Usage: php artisan db:seed --class=DeadlineSeeder --duration=7 (for 7 days)
     */
    public function runWithCustomDuration(int $durationDays = 1): void
    {
        $categories = [
            'student_verification',
            'student_assessment_form', 
            'hte_assessment_form',
            'internship_placement'
        ];

        $now = Carbon::now();
        
        foreach ($categories as $category) {
            $startDate = $now->copy();
            $endDate = $now->copy()->addDays($durationDays);
            
            // Check if deadline already exists for this category
            $existingDeadline = Deadline::where('category', $category)->first();
            
            if (!$existingDeadline) {
                Deadline::create([
                    'title' => $this->getTitleForCategory($category),
                    'category' => $category,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'active',
                ]);
                
                $this->command->info("Created {$durationDays}-day deadline for category: {$category}");
            } else {
                $this->command->warn("Deadline already exists for category: {$category}");
            }
        }
    }
}
