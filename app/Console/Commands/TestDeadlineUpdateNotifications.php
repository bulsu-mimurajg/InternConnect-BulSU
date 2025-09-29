<?php

namespace App\Console\Commands;

use App\Models\Deadline;
use App\Models\User;
use App\Services\CentralizedDeadlineNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestDeadlineUpdateNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:deadline-update-notifications {email} {--deadline-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test deadline update notifications to verify time remaining is updated correctly';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $deadlineId = $this->option('deadline-id');

        $this->info('Testing Deadline Update Notifications');
        $this->info('=====================================');

        // Find or create a test deadline
        if ($deadlineId) {
            $deadline = Deadline::find($deadlineId);
            if (!$deadline) {
                $this->error("Deadline with ID {$deadlineId} not found.");
                return 1;
            }
        } else {
            // Create a test deadline
            $deadline = Deadline::create([
                'title' => 'Test Deadline Update',
                'category' => 'student_assessment_form',
                'start_date' => now()->subDay(),
                'end_date' => now()->addDays(3), // 3 days remaining
            ]);
            $this->info("Created test deadline with ID: {$deadline->id}");
        }

        $this->info("Testing with deadline: {$deadline->title}");
        $this->info("Current end date: {$deadline->end_date}");
        $this->info("Days remaining: " . now()->diffInDays($deadline->end_date, false));

        // Find a test user
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User with email {$email} not found.");
            return 1;
        }

        $this->info("Testing with user: {$user->email} (Role: {$user->getRoleNames()->first()})");

        // Test 1: Send initial notification
        $this->info("\n1. Sending initial notification...");
        $service = new CentralizedDeadlineNotificationService();
        $service->queueDeadlineNotificationsForDeadline($deadline);
        $this->info("   ✅ Initial notification queued");

        // Test 2: Update deadline to 1 day remaining
        $this->info("\n2. Updating deadline to 1 day remaining...");
        $deadline->update([
            'end_date' => now()->addDay(), // 1 day remaining
        ]);
        $this->info("   Updated end date: {$deadline->end_date}");
        $this->info("   Days remaining: " . now()->diffInDays($deadline->end_date, false));

        $service->queueDeadlineNotificationsForDeadline($deadline);
        $this->info("   ✅ Updated notification queued (1 day remaining)");

        // Test 3: Update deadline to 5 days remaining
        $this->info("\n3. Updating deadline to 5 days remaining...");
        $deadline->update([
            'end_date' => now()->addDays(5), // 5 days remaining
        ]);
        $this->info("   Updated end date: {$deadline->end_date}");
        $this->info("   Days remaining: " . now()->diffInDays($deadline->end_date, false));

        $service->queueDeadlineNotificationsForDeadline($deadline);
        $this->info("   ✅ Updated notification queued (5 days remaining)");

        // Test 4: Update deadline to critical (2 hours remaining)
        $this->info("\n4. Updating deadline to critical (2 hours remaining)...");
        $deadline->update([
            'end_date' => now()->addHours(2), // 2 hours remaining
        ]);
        $this->info("   Updated end date: {$deadline->end_date}");
        $this->info("   Hours remaining: " . now()->diffInHours($deadline->end_date, false));

        $service->queueDeadlineNotificationsForDeadline($deadline);
        $this->info("   ✅ Updated notification queued (2 hours remaining)");

        $this->info("\nTest Summary:");
        $this->info("=============");
        $this->info("✅ Initial notification (3 days)");
        $this->info("✅ Updated notification (1 day)");
        $this->info("✅ Updated notification (5 days)");
        $this->info("✅ Updated notification (2 hours - critical)");
        $this->info("\nCheck your email for the notifications with updated time information!");
        $this->info("Each email should show the correct time remaining based on the deadline update.");

        // Clean up test deadline if we created it
        if (!$this->option('deadline-id')) {
            $deadline->delete();
            $this->info("\n🧹 Cleaned up test deadline");
        }

        return 0;
    }
}