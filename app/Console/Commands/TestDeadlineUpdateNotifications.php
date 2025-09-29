<?php

namespace App\Console\Commands;

use App\Models\Deadline;
use App\Models\User;
use App\Services\CentralizedDeadlineNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestDeadlineUpdateNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:deadline-update-notifications {deadline_id} {days_remaining}';

    /**
     * The console command description.
     */
    protected $description = 'Test deadline update notifications with specific days remaining';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deadlineId = $this->argument('deadline_id');
        $daysRemaining = $this->argument('days_remaining');

        $this->info("Testing deadline update notifications for deadline ID: {$deadlineId} with {$daysRemaining} days remaining");

        // Get the deadline
        $deadline = Deadline::find($deadlineId);
        if (!$deadline) {
            $this->error("Deadline with ID {$deadlineId} not found");
            return 1;
        }

        // Update the deadline to have the specified days remaining
        $newEndDate = Carbon::now()->addDays($daysRemaining);
        $deadline->update(['end_date' => $newEndDate]);

        $this->info("Updated deadline '{$deadline->title}' to end on: {$newEndDate->format('Y-m-d H:i:s')}");

        // Get a test user (admin user)
        $user = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->first();

        if (!$user) {
            $this->error("No admin user found for testing");
            return 1;
        }

        $this->info("Using test user: {$user->email} (ID: {$user->id})");

        // Test the new method
        $service = new CentralizedDeadlineNotificationService();
        
        $this->info("Testing processDeadlineNotificationForUserAndDeadline method...");
        
        try {
            $service->processDeadlineNotificationForUserAndDeadline($user, $deadline, 'admin');
            $this->info("✅ Notification sent successfully!");
        } catch (\Exception $e) {
            $this->error("❌ Error sending notification: " . $e->getMessage());
            return 1;
        }

        // Show the current time remaining calculation
        $now = Carbon::now();
        $deadlineDate = Carbon::parse($deadline->end_date);
        $timeRemaining = $now->diffInHours($deadlineDate, false);
        $daysRemainingCalculated = round($now->diffInDays($deadlineDate, false));

        $this->info("Current time remaining calculation:");
        $this->info("- Hours remaining: {$timeRemaining}");
        $this->info("- Days remaining: {$daysRemainingCalculated}");

        // Test the notification data generation
        $notificationData = $this->getNotificationDataForDeadline($deadline, $timeRemaining, $daysRemainingCalculated);
        
        if ($notificationData) {
            $this->info("Notification data generated:");
            $this->info("- Title: {$notificationData['title']}");
            $this->info("- Message: {$notificationData['message']}");
            $this->info("- Days remaining in data: {$notificationData['data']['days_remaining']}");
        } else {
            $this->info("No notification data generated (deadline not in 1, 3, 5 days or < 24 hours)");
        }

        return 0;
    }

    /**
     * Get notification data for deadline based on time remaining (copied from service for testing)
     */
    private function getNotificationDataForDeadline(Deadline $deadline, int $hoursRemaining, int $daysRemaining): ?array
    {
        $deadlineName = $deadline->title ?? $this->getDefaultDeadlineName($deadline->category);
        
        // Check for specific day reminders (1, 3, 5 days) - PRIORITY
        if (in_array($daysRemaining, [1, 3, 5])) {
            $urgencyLevel = $this->getUrgencyLevel($daysRemaining, null, $deadline->category);
            
            return [
                'title' => $urgencyLevel['title'],
                'message' => "{$deadlineName} deadline is in {$daysRemaining} day(s). {$urgencyLevel['message']}",
                'data' => [
                    'deadline_id' => $deadline->id,
                    'deadline_name' => $deadlineName,
                    'days_remaining' => $daysRemaining,
                    'deadline_date' => $deadline->end_date,
                    'category' => $deadline->category,
                    'urgency_level' => $urgencyLevel['level'],
                    'is_critical' => $urgencyLevel['is_critical'],
                ],
            ];
        }
        
        // If less than 24 hours remaining (within the day), show hours
        if ($hoursRemaining < 24 && $hoursRemaining > 0) {
            $urgencyLevel = $this->getUrgencyLevel(null, $hoursRemaining, $deadline->category);
            
            return [
                'title' => $urgencyLevel['title'],
                'message' => "{$deadlineName} deadline is in {$hoursRemaining} hours. {$urgencyLevel['message']}",
                'data' => [
                    'deadline_id' => $deadline->id,
                    'deadline_name' => $deadlineName,
                    'hours_remaining' => $hoursRemaining,
                    'deadline_date' => $deadline->end_date,
                    'category' => $deadline->category,
                    'urgency_level' => $urgencyLevel['level'],
                    'is_critical' => $urgencyLevel['is_critical'],
                ],
            ];
        }
        
        return null; // No notification needed
    }

    /**
     * Get urgency level based on time remaining and deadline category (copied from service for testing)
     */
    private function getUrgencyLevel($daysRemaining, $hoursRemaining, $category): array
    {
        $isCritical = false;
        $level = 'normal';
        $title = 'Deadline Reminder';
        $message = '';

        if ($daysRemaining !== null) {
            if ($daysRemaining <= 0) {
                $isCritical = true;
                $level = 'expired';
                $title = 'Deadline Expired!';
                $message = 'This deadline has passed. Please contact the administrator immediately.';
            } elseif ($daysRemaining == 1) {
                $isCritical = true;
                $level = 'urgent';
                $title = 'Deadline Tomorrow!';
                $message = 'This is your final reminder. Please complete the required actions immediately.';
            } elseif ($daysRemaining <= 3) {
                $isCritical = true;
                $level = 'high';
                $title = 'Deadline Approaching!';
                $message = 'Please prioritize this task and complete it soon.';
            } elseif ($daysRemaining <= 5) {
                $level = 'medium';
                $title = 'Deadline Reminder';
                $message = 'Please plan to complete this task in the coming days.';
            }
        } elseif ($hoursRemaining !== null && $hoursRemaining < 24) {
            if ($hoursRemaining <= 0) {
                $isCritical = true;
                $level = 'expired';
                $title = 'Deadline Expired!';
                $message = 'This deadline has passed. Please contact the administrator immediately.';
            } elseif ($hoursRemaining <= 2) {
                $isCritical = true;
                $level = 'critical';
                $title = 'Deadline in Hours!';
                $message = 'This is extremely urgent. Please complete the required actions immediately.';
            } else {
                $isCritical = true;
                $level = 'urgent';
                $title = 'Deadline Today!';
                $message = 'Please complete the required actions as soon as possible.';
            }
        }

        return [
            'level' => $level,
            'is_critical' => $isCritical,
            'title' => $title,
            'message' => $message,
        ];
    }

    /**
     * Get default deadline name based on category (copied from service for testing)
     */
    private function getDefaultDeadlineName(string $category): string
    {
        return match($category) {
            'student_verification' => 'Student Verification',
            'student_assessment_form' => 'Student Assessment Form',
            'hte_assessment_form' => 'HTE Assessment Form',
            'sip_endorsement' => 'SIP Endorsement',
            'student_placements_by_hte' => 'Student Placements',
            default => 'Assessment Form',
        };
    }
}