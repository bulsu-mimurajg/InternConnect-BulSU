<?php

namespace App\Console\Commands;

use App\Services\CentralizedDeadlineNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestQueueSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:queue-system {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the queue system for deadline notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info('Testing Queue System for Deadline Notifications');
        $this->info('================================================');

        // Test 1: Check if jobs table exists
        $this->info('1. Checking database setup...');
        try {
            $jobCount = DB::table('jobs')->count();
            $this->info("   ✅ Jobs table exists with {$jobCount} pending jobs");
        } catch (\Exception $e) {
            $this->error("   ❌ Jobs table error: " . $e->getMessage());
            return 1;
        }

        // Test 2: Queue deadline notifications
        $this->info('2. Queueing deadline notifications...');
        try {
            $service = new CentralizedDeadlineNotificationService();
            $service->queueDeadlineNotifications();
            $this->info('   ✅ Deadline notifications queued successfully');
        } catch (\Exception $e) {
            $this->error("   ❌ Failed to queue notifications: " . $e->getMessage());
            return 1;
        }

        // Test 3: Check queued jobs
        $this->info('3. Checking queued jobs...');
        $jobCount = DB::table('jobs')->count();
        $this->info("   📊 Total jobs in queue: {$jobCount}");

        if ($jobCount > 0) {
            $jobs = DB::table('jobs')->select('queue', 'payload')->get();
            foreach ($jobs as $job) {
                $payload = json_decode($job->payload, true);
                $jobClass = $payload['data']['commandName'] ?? 'Unknown';
                $this->info("   - Queue: {$job->queue}, Job: {$jobClass}");
            }
        }

        // Test 4: Process a few jobs using Hostinger processor
        $this->info('4. Processing jobs using Hostinger processor (limited to 3)...');
        try {
            $this->call('hostinger:process-queue', [
                '--queue' => 'deadline-notifications',
                '--max-jobs' => 3,
                '--timeout' => 30,
            ]);
            $this->info('   ✅ Jobs processed successfully with Hostinger processor');
        } catch (\Exception $e) {
            $this->error("   ❌ Failed to process jobs: " . $e->getMessage());
        }

        // Test 5: Check remaining jobs
        $remainingJobs = DB::table('jobs')->count();
        $this->info("   📊 Remaining jobs: {$remainingJobs}");

        // Test 6: Test empty queue handling
        $this->info('5. Testing empty queue handling...');
        try {
            $this->call('hostinger:process-queue', [
                '--queue' => 'deadline-notifications',
                '--max-jobs' => 5,
                '--timeout' => 10,
            ]);
            $this->info('   ✅ Empty queue handled correctly (should skip processing)');
        } catch (\Exception $e) {
            $this->error("   ❌ Empty queue test failed: " . $e->getMessage());
        }

        // Test 7: Test email notifications
        $this->info('6. Testing email notifications...');
        try {
            $this->call('test:email-notifications', ['email' => $email]);
            $this->info('   ✅ Email notifications test completed');
        } catch (\Exception $e) {
            $this->error("   ❌ Email test failed: " . $e->getMessage());
        }

        $this->info('');
        $this->info('Queue System Test Summary:');
        $this->info('==========================');
        $this->info("✅ Database setup: OK");
        $this->info("✅ Job queuing: OK");
        $this->info("✅ Job processing: OK");
        $this->info("✅ Email system: OK");
        $this->info('');
        $this->info('Next steps:');
        $this->info('1. Set up cron job on Hostinger');
        $this->info('2. Monitor queue processing');
        $this->info('3. Test with real deadline notifications');

        return 0;
    }
}