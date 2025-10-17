<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AutomaticEndorsementService;
use App\Services\AutomaticPlacementService;

class ProcessAutomaticDeadlines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deadlines:process {--type=all : Type of deadline to process (sip, hte, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process automatic deadlines for SIP endorsements and HTE placements';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        
        $this->info('Processing automatic deadlines...');
        
        if ($type === 'all' || $type === 'sip') {
            $this->processSipEndorsements();
        }
        
        if ($type === 'all' || $type === 'hte') {
            $this->processHtePlacements();
        }
        
        $this->info('Automatic deadline processing completed!');
    }

    /**
     * Process SIP endorsements
     */
    private function processSipEndorsements(): void
    {
        $this->info('Processing SIP endorsements...');
        
        $service = new AutomaticEndorsementService();
        $results = $service->processSipEndorsements();
        
        $this->info("SIP Endorsements Results:");
        $this->info("- Endorsed: {$results['endorsed_count']} students");
        $this->info("- Skipped: {$results['skipped_count']} students");
        
        if (!empty($results['errors'])) {
            $this->error("Errors encountered:");
            foreach ($results['errors'] as $error) {
                $this->error("- {$error}");
            }
        }
    }

    /**
     * Process HTE placements
     */
    private function processHtePlacements(): void
    {
        $this->info('Processing HTE placements...');
        
        $service = new AutomaticPlacementService();
        $results = $service->processHtePlacements();
        
        $this->info("HTE Placements Results:");
        $this->info("- Placed: {$results['placed_count']} students");
        $this->info("- Skipped: {$results['skipped_count']} students");
        
        if (!empty($results['errors'])) {
            $this->error("Errors encountered:");
            foreach ($results['errors'] as $error) {
                $this->error("- {$error}");
            }
        }
    }
}
