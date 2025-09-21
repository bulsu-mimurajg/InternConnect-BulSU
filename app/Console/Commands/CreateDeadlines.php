<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Deadline;
use Carbon\Carbon;

class CreateDeadlines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deadlines:create 
                            {--duration=1 : Duration in days for the deadlines}
                            {--force : Force create even if deadlines exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create deadlines for all categories with specified duration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $duration = (int) $this->option('duration');
        $force = $this->option('force');
        
        $categories = [
            'student_verification',
            'student_assessment_form', 
            'hte_assessment_form'
        ];

        $now = Carbon::now();
        $created = 0;
        $skipped = 0;
        
        $this->info("Creating deadlines with {$duration} day(s) duration...");
        
        foreach ($categories as $category) {
            $startDate = $now->copy();
            $endDate = $now->copy()->addDays($duration);
            
            // Check if deadline already exists for this category
            $existingDeadline = Deadline::where('category', $category)->first();
            
            if (!$existingDeadline || $force) {
                if ($existingDeadline && $force) {
                    $existingDeadline->delete();
                    $this->warn("Deleted existing deadline for category: {$category}");
                }
                
                Deadline::create([
                    'title' => $this->getTitleForCategory($category),
                    'category' => $category,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'active',
                ]);
                
                $this->info("✓ Created {$duration}-day deadline for category: {$category}");
                $created++;
            } else {
                $this->warn("⚠ Deadline already exists for category: {$category} (use --force to replace)");
                $skipped++;
            }
        }
        
        $this->newLine();
        $this->info("Summary:");
        $this->info("Created: {$created} deadlines");
        $this->info("Skipped: {$skipped} deadlines");
        
        if ($skipped > 0) {
            $this->newLine();
            $this->comment("Use --force flag to replace existing deadlines");
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
            default => ucwords(str_replace('_', ' ', $category)) . ' Deadline',
        };
    }
}
