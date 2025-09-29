<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessQueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:process 
                            {--queue=deadline-notifications : The queue to process}
                            {--timeout=60 : The number of seconds a child process can run}
                            {--tries=3 : Number of times to attempt a job before logging it failed}
                            {--max-jobs=20 : Number of jobs to process before stopping}
                            {--check-empty : Only run if queue is not empty}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process the queue for deadline notifications (optimized for Hostinger)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $queue = $this->option('queue');
        $timeout = $this->option('timeout');
        $tries = $this->option('tries');
        $maxJobs = $this->option('max-jobs');
        $checkEmpty = $this->option('check-empty');

        // Check if queue is empty before processing
        if ($checkEmpty) {
            $jobCount = \DB::table('jobs')->where('queue', $queue)->count();
            
            if ($jobCount === 0) {
                $this->info("Queue '{$queue}' is empty. Skipping processing.");
                Log::info('Queue processing skipped - no jobs', ['queue' => $queue]);
                return 0;
            }
            
            $this->info("Found {$jobCount} jobs in queue '{$queue}'. Processing...");
        }

        $this->info("Starting queue processing for: {$queue}");
        $this->info("Timeout: {$timeout}s, Tries: {$tries}, Max Jobs: {$maxJobs}");

        try {
            // Process the queue with specified options
            $this->call('queue:work', [
                '--queue' => $queue,
                '--timeout' => $timeout,
                '--tries' => $tries,
                '--max-jobs' => $maxJobs,
                '--stop-when-empty' => true,
                '--verbose' => false, // Reduced verbosity for cron
            ]);

            $this->info('Queue processing completed successfully.');
            Log::info('Queue processing completed via ProcessQueueCommand', [
                'queue' => $queue,
                'timeout' => $timeout,
                'tries' => $tries,
                'max_jobs' => $maxJobs,
                'jobs_processed' => $maxJobs,
            ]);

        } catch (\Exception $e) {
            $this->error('Queue processing failed: ' . $e->getMessage());
            Log::error('Queue processing failed via ProcessQueueCommand', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }

        return 0;
    }
}