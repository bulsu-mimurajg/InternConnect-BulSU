<?php

namespace App\Console\Commands;

use App\Services\InternshipSeasonService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateSeasonStatusesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seasons:update-statuses {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update internship season statuses based on start/end dates and deadline requirements';

    /**
     * Execute the console command.
     */
    public function handle(InternshipSeasonService $seasonService): int
    {
        $this->info('Checking internship season statuses...');
        
        if ($this->option('dry-run')) {
            $this->info('DRY RUN MODE - No changes will be made');
            $this->line('');
            
            $needsAttention = $seasonService->getSeasonsNeedingAttention();
            
            if (empty($needsAttention)) {
                $this->info('✅ All seasons have correct statuses');
                return self::SUCCESS;
            }
            
            $this->warn('⚠️  Seasons that need status updates:');
            $this->line('');
            
            foreach ($needsAttention as $item) {
                $season = $item['season'];
                $this->line("Season: {$season->name}");
                $this->line("  Current Status: {$item['current_status']}");
                $this->line("  Should Be: {$item['should_be_status']}");
                $this->line("  Reason: {$item['reason']}");
                $this->line("  Can Auto-Fix: " . ($item['can_auto_fix'] ? 'Yes' : 'No'));
                $this->line('');
            }
            
            return self::SUCCESS;
        }
        
        try {
            $results = $seasonService->checkAndUpdateSeasonStatuses();
            
            // Display results
            if (!empty($results['activated'])) {
                $this->info('✅ Automatically activated seasons:');
                foreach ($results['activated'] as $season) {
                    $this->line("  - {$season['name']} (ID: {$season['id']}) - {$season['reason']}");
                }
                $this->line('');
            }
            
            if (!empty($results['deactivated'])) {
                $this->info('✅ Automatically deactivated seasons:');
                foreach ($results['deactivated'] as $season) {
                    $this->line("  - {$season['name']} (ID: {$season['id']}) - {$season['reason']}");
                }
                $this->line('');
            }
            
            if (!empty($results['errors'])) {
                $this->error('❌ Errors occurred:');
                foreach ($results['errors'] as $error) {
                    if (isset($error['season_name'])) {
                        $this->line("  - {$error['season_name']} (ID: {$error['season_id']}): {$error['error']}");
                    } else {
                        $this->line("  - {$error['error']}");
                    }
                }
                $this->line('');
            }
            
            $totalChanges = count($results['activated']) + count($results['deactivated']);
            
            if ($totalChanges === 0 && empty($results['errors'])) {
                $this->info('✅ All seasons have correct statuses - no changes needed');
            } else {
                $this->info("📊 Summary: {$totalChanges} seasons updated, " . count($results['errors']) . " errors");
            }
            
            return self::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("❌ Failed to update season statuses: {$e->getMessage()}");
            Log::error('Season status update command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return self::FAILURE;
        }
    }
}
