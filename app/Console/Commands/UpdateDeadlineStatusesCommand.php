<?php

namespace App\Console\Commands;

use App\Models\Deadline;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateDeadlineStatusesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deadlines:update-statuses {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update deadline statuses based on start/end dates';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking deadline statuses...');
        
        if ($this->option('dry-run')) {
            $this->info('DRY RUN MODE - No changes will be made');
            $this->line('');
            
            $needsAttention = Deadline::getDeadlinesNeedingAttention();
            
            if (empty($needsAttention)) {
                $this->info('✅ All deadlines have correct statuses');
                return self::SUCCESS;
            }
            
            $this->warn('⚠️  Deadlines that need status updates:');
            $this->line('');
            
            foreach ($needsAttention as $item) {
                $deadline = $item['deadline'];
                $this->line("Deadline: {$deadline->title}");
                $this->line("  Category: {$deadline->category}");
                $this->line("  Current Status: {$item['current_status']}");
                $this->line("  Should Be: {$item['should_be_status']}");
                $this->line("  Reason: {$item['reason']}");
                $this->line("  Start Date: {$deadline->start_date->format('Y-m-d H:i')}");
                $this->line("  End Date: {$deadline->end_date->format('Y-m-d H:i')}");
                $this->line('');
            }
            
            return self::SUCCESS;
        }
        
        try {
            $results = Deadline::checkAndUpdateStatuses();
            
            // Display results
            if (!empty($results['activated'])) {
                $this->info('✅ Automatically activated deadlines:');
                foreach ($results['activated'] as $deadline) {
                    $this->line("  - {$deadline['title']} ({$deadline['category']}) - {$deadline['reason']}");
                }
                $this->line('');
            }
            
            if (!empty($results['deactivated'])) {
                $this->info('✅ Automatically deactivated deadlines:');
                foreach ($results['deactivated'] as $deadline) {
                    $this->line("  - {$deadline['title']} ({$deadline['category']}) - {$deadline['reason']}");
                }
                $this->line('');
            }
            
            if (!empty($results['expired'])) {
                $this->info('✅ Automatically expired deadlines:');
                foreach ($results['expired'] as $deadline) {
                    $this->line("  - {$deadline['title']} ({$deadline['category']}) - {$deadline['reason']}");
                }
                $this->line('');
            }
            
            if (!empty($results['errors'])) {
                $this->error('❌ Errors occurred:');
                foreach ($results['errors'] as $error) {
                    if (isset($error['deadline_title'])) {
                        $this->line("  - {$error['deadline_title']} (ID: {$error['deadline_id']}): {$error['error']}");
                    } else {
                        $this->line("  - {$error['error']}");
                    }
                }
                $this->line('');
            }
            
            $totalChanges = count($results['activated']) + count($results['deactivated']) + count($results['expired']);
            
            if ($totalChanges === 0 && empty($results['errors'])) {
                $this->info('✅ All deadlines have correct statuses - no changes needed');
            } else {
                $this->info("📊 Summary: {$totalChanges} deadlines updated, " . count($results['errors']) . " errors");
            }
            
            return self::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("❌ Failed to update deadline statuses: {$e->getMessage()}");
            Log::error('Deadline status update command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return self::FAILURE;
        }
    }
}
