<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckQueueStatus extends Command
{
    protected $signature = 'queue:status';
    protected $description = 'Check queue status and configuration';

    public function handle()
    {
        $this->info('📊 Queue Status Check');
        $this->newLine();

        // Check queue configuration
        $queueDriver = config('queue.default');
        $this->line("Queue Driver: {$queueDriver}");

        if ($queueDriver === 'database') {
            // Check jobs table
            try {
                $totalJobs = DB::table('jobs')->count();
                $this->line("Total jobs in queue: {$totalJobs}");

                if ($totalJobs > 0) {
                    $this->warn("⚠️  You have {$totalJobs} jobs waiting to be processed!");
                    $this->line("Run: php artisan queue:work");
                } else {
                    $this->line("✅ No pending jobs");
                }

                // Check failed jobs
                $failedJobs = DB::table('failed_jobs')->count();
                if ($failedJobs > 0) {
                    $this->error("❌ {$failedJobs} failed jobs found!");
                    $this->line("Run: php artisan queue:retry all");
                } else {
                    $this->line("✅ No failed jobs");
                }

            } catch (\Exception $e) {
                $this->error("❌ Cannot access jobs table: " . $e->getMessage());
                $this->line("Run: php artisan migrate");
            }
        } elseif ($queueDriver === 'sync') {
            $this->line("ℹ️  Using sync driver - emails will be sent immediately");
        } else {
            $this->line("ℹ️  Using {$queueDriver} driver");
        }

        $this->newLine();
        $this->info('Queue status check complete!');
        
        return 0;
    }
}

