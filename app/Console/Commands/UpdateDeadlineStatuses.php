<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Deadline;
use App\Services\InternshipSeasonService;
use Carbon\Carbon;

class UpdateDeadlineStatuses extends Command
{
    public function __construct(
        private InternshipSeasonService $seasonService
    ) {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deadlines:update-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update deadline statuses based on current time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating deadline statuses...');
        
        $now = Carbon::now();
        $updatedCount = 0;
        
        // Get all deadlines that need status updates
        $deadlines = Deadline::where(function($query) use ($now) {
            $query->where(function($q) use ($now) {
                // Active deadlines that should be expired
                $q->where('status', 'active')
                  ->where('end_date', '<=', $now);
            })->orWhere(function($q) use ($now) {
                // Expired deadlines that should be active (if end_date is in future)
                $q->where('status', 'expired')
                  ->where('end_date', '>', $now);
            });
        })->get();
        
        foreach ($deadlines as $deadline) {
            $oldStatus = $deadline->status;
            
            // Update status based on end_date
            if ($deadline->end_date <= $now) {
                $deadline->status = 'expired';
            } else {
                $deadline->status = 'active';
            }
            
            // Only update if status changed
            if ($oldStatus !== $deadline->status) {
                $deadline->save();
                $updatedCount++;
                
                $this->line("Updated deadline '{$deadline->title}' ({$deadline->category}) from '{$oldStatus}' to '{$deadline->status}'");
                
                // Check if this is an archive students deadline that just expired
                if ($deadline->isArchiveStudentsDeadline() && $deadline->status === 'expired') {
                    $this->info("Archive students deadline expired. Triggering automatic archiving...");
                    $this->seasonService->handleArchiveDeadlineExpiration($deadline->internship_season_id);
                }
                
                // Check if this is an internship placement deadline that just expired
                if ($deadline->category === 'internship_placement' && $deadline->status === 'expired') {
                    $this->info("Internship placement deadline expired. Triggering automatic placement...");
                    $this->triggerAutomaticPlacement();
                }
            }
        }
        
        if ($updatedCount > 0) {
            $this->info("Updated {$updatedCount} deadline(s)");
        } else {
            $this->info('No deadlines needed status updates');
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Trigger the complete 3-tier automatic placement process
     */
    private function triggerAutomaticPlacement(): void
    {
        $this->info('Starting 3-tier automatic placement...');
        $placementService = app(\App\Services\AutomaticPlacementService::class);
        
        // Tier 1: Endorsed students (no forceRun - uses timing check)
        $this->info('Tier 1: Processing endorsed students...');
        $tier1Results = $placementService->processEndorsedStudentsPlacements();
        $this->line("  Placed: {$tier1Results['placed_count']}");
        
        // Tier 2: Matched students (no forceRun - uses timing check)
        $this->info('Tier 2: Processing matched students...');
        $tier2Results = $placementService->processMatchedStudentsPlacements();
        $this->line("  Placed: {$tier2Results['matched_placed_count']}");
        
        // Tier 3: Emergency placement (no forceRun - uses timing check)
        $this->info('Tier 3: Emergency placements...');
        $tier3Results = $placementService->processEmergencyPlacements();
        $this->line("  Placed: {$tier3Results['emergency_placed_count']}");
        
        $total = $tier1Results['placed_count'] + $tier2Results['matched_placed_count'] + $tier3Results['emergency_placed_count'];
        $this->info("Total placed: {$total}");
    }
}
