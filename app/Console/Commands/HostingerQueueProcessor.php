<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HostingerQueueProcessor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hostinger:process-queue 
                            {--queue=deadline-notifications : The queue to process}
                            {--max-jobs=15 : Maximum jobs to process per run}
                            {--timeout=45 : Maximum execution time in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hostinger-optimized queue processor that only runs when jobs exist';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $queue = $this->option('queue');
        $maxJobs = (int) $this->option('max-jobs');
        $timeout = (int) $this->option('timeout');

        // Start timing
        $startTime = microtime(true);

        // Check if there are any jobs in the queue
        $jobCount = DB::table('jobs')->where('queue', $queue)->count();
        
        if ($jobCount === 0) {
            // No jobs to process, exit quickly
            Log::info('Hostinger queue processor: No jobs found', ['queue' => $queue]);
            return 0;
        }

        $this->info("Found {$jobCount} jobs in queue '{$queue}'. Processing up to {$maxJobs} jobs...");

        // Process jobs with strict limits
        $processed = 0;
        $startTime = time();

        try {
            while ($processed < $maxJobs) {
                // Check timeout
                if ((time() - $startTime) >= $timeout) {
                    $this->warn("Timeout reached ({$timeout}s). Stopping processing.");
                    break;
                }

                // Get one job
                $job = DB::table('jobs')
                    ->where('queue', $queue)
                    ->orderBy('id')
                    ->first();

                if (!$job) {
                    // No more jobs
                    break;
                }

                // Process the job
                $this->processJob($job);
                $processed++;

                // Small delay to prevent overwhelming the system
                usleep(100000); // 0.1 second delay
            }

            $executionTime = round(microtime(true) - $startTime, 2);
            $remainingJobs = DB::table('jobs')->where('queue', $queue)->count();

            $this->info("Processed {$processed} jobs in {$executionTime}s. {$remainingJobs} jobs remaining.");
            
            Log::info('Hostinger queue processing completed', [
                'queue' => $queue,
                'processed' => $processed,
                'remaining' => $remainingJobs,
                'execution_time' => $executionTime,
            ]);

        } catch (\Exception $e) {
            $this->error('Queue processing failed: ' . $e->getMessage());
            Log::error('Hostinger queue processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }

        return 0;
    }

    /**
     * Process a single job
     */
    private function processJob($job)
    {
        try {
            // Decode job payload
            $payload = json_decode($job->payload, true);
            $commandName = $payload['data']['commandName'] ?? 'Unknown';
            
            $this->line("Processing job: {$commandName}");

            // Use Laravel's built-in job processing
            $this->call('queue:work', [
                '--queue' => $job->queue,
                '--max-jobs' => 1,
                '--timeout' => 30,
                '--stop-when-empty' => true,
                '--verbose' => false,
            ]);

        } catch (\Exception $e) {
            $this->error("Failed to process job {$job->id}: " . $e->getMessage());
            Log::error('Failed to process individual job', [
                'job_id' => $job->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}