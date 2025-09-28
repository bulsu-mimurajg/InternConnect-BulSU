<?php

namespace App\Services;

use App\Models\Deadline;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DeadlineNotificationService
{
    /**
     * Check and create/update deadline notifications for HTE users
     */
    public function checkAndCreateDeadlineNotifications(): void
    {
        // Get all HTE users
        $hteUsers = User::role('hte')->get();

        foreach ($hteUsers as $hteUser) {
            $this->processDeadlineNotificationsForUser($hteUser);
        }
    }

    /**
     * Process deadline notifications for a specific HTE user
     */
    private function processDeadlineNotificationsForUser(User $hteUser): void
    {
        // Get HTE deadlines (both assessment form and student placements)
        $hteDeadlines = Deadline::whereIn('category', ['hte_assessment_form', 'student_placements_by_hte'])->get();

        foreach ($hteDeadlines as $deadline) {
            $this->processDeadlineForUser($hteUser, $deadline);
        }
    }

    /**
     * Process a specific deadline for a user
     */
    private function processDeadlineForUser(User $hteUser, Deadline $deadline): void
    {
        // Check if this is an HTE assessment form deadline
        if ($deadline->category === 'hte_assessment_form') {
            // Check if the HTE user has already submitted their assessment form
            if ($hteUser->hte && $hteUser->hte->is_submit) {
                // User has already submitted, remove any existing notifications and skip
                $this->removeExistingDeadlineNotifications($hteUser->id, $deadline->id);
                return;
            }
        }
        
        // For student placements by HTE deadlines, show notifications to all HTE users
        // regardless of their assessment submission status

        $now = Carbon::now();
        $deadlineDate = Carbon::parse($deadline->end_date);
        
        // Calculate time remaining
        $timeRemaining = $now->diffInHours($deadlineDate, false);
        $daysRemaining = round($now->diffInDays($deadlineDate, false));
        
        // Only process if deadline is in the future
        if ($timeRemaining <= 0) {
            return;
        }

        $notificationData = $this->getNotificationDataForDeadline($deadline, $timeRemaining, $daysRemaining);
        
        if (!$notificationData) {
            return; // No notification needed
        }

        // Check if notification already exists for this deadline
        $existingNotification = Notification::where('user_id', $hteUser->id)
            ->where('type', 'hte_deadline')
            ->whereJsonContains('data->deadline_id', $deadline->id)
            ->first();

        if ($existingNotification) {
            // Update existing notification
            $existingNotification->update([
                'title' => $notificationData['title'],
                'message' => $notificationData['message'],
                'data' => $notificationData['data'],
                'updated_at' => now(),
            ]);
            
            Log::info("Updated deadline notification for HTE user {$hteUser->id}, deadline {$deadline->id}");
        } else {
            // Create new notification
            Notification::create([
                'user_id' => $hteUser->id,
                'type' => 'hte_deadline',
                'title' => $notificationData['title'],
                'message' => $notificationData['message'],
                'data' => $notificationData['data'],
                'is_read' => false,
            ]);
            
            Log::info("Created deadline notification for HTE user {$hteUser->id}, deadline {$deadline->id}");
        }
    }

    /**
     * Get notification data based on time remaining
     */
    private function getNotificationDataForDeadline(Deadline $deadline, int $hoursRemaining, int $daysRemaining): ?array
    {
        $deadlineName = $deadline->title ?? 'Assessment Form';
        
        // Check for specific day reminders (1, 3, 5 days) - PRIORITY
        if (in_array($daysRemaining, [1, 3, 5])) {
            $urgencyLevel = $this->getUrgencyLevel($daysRemaining, $deadline->category);
            $message = $deadline->category === 'student_placements_by_hte'
                ? "Student placements deadline is in {$daysRemaining} day(s). {$urgencyLevel['message']}"
                : "HTE {$deadlineName} deadline is in {$daysRemaining} day(s). {$urgencyLevel['message']}";
            
            return [
                'title' => $urgencyLevel['title'],
                'message' => $message,
                'data' => [
                    'deadline_id' => $deadline->id,
                    'deadline_name' => $deadlineName,
                    'days_remaining' => $daysRemaining,
                    'deadline_date' => $deadline->end_date,
                    'category' => $deadline->category,
                ],
            ];
        }
        
        // If less than 24 hours remaining (within the day), show hours
        if ($hoursRemaining < 24 && $hoursRemaining > 0) {
            $message = $deadline->category === 'student_placements_by_hte' 
                ? "Student placements deadline is in {$hoursRemaining} hours. Please complete your placements soon!"
                : "HTE {$deadlineName} deadline is in {$hoursRemaining} hours. Please submit your form soon!";
                
            return [
                'title' => 'Deadline Approaching!',
                'message' => $message,
                'data' => [
                    'deadline_id' => $deadline->id,
                    'deadline_name' => $deadlineName,
                    'hours_remaining' => $hoursRemaining,
                    'deadline_date' => $deadline->end_date,
                    'category' => $deadline->category,
                ],
            ];
        }
        
        return null; // No notification needed
    }

    /**
     * Get urgency level based on days remaining and deadline category
     */
    private function getUrgencyLevel(int $daysRemaining, string $category): array
    {
        $isPlacement = $category === 'student_placements_by_hte';
        
        switch ($daysRemaining) {
            case 1:
                return [
                    'title' => '⚠️ Deadline Tomorrow!',
                    'message' => $isPlacement 
                        ? 'Please complete your student placements immediately to avoid missing the deadline.'
                        : 'Please submit your assessment form immediately to avoid missing the deadline.',
                ];
            case 3:
                return [
                    'title' => 'Deadline in 3 Days',
                    'message' => $isPlacement
                        ? 'Please prepare and complete your student placements soon.'
                        : 'Please prepare and submit your assessment form soon.',
                ];
            case 5:
                return [
                    'title' => 'Deadline in 5 Days',
                    'message' => $isPlacement
                        ? 'Please start preparing your student placements.'
                        : 'Please start preparing your assessment form.',
                ];
            default:
                return [
                    'title' => 'Deadline Reminder',
                    'message' => $isPlacement
                        ? 'Please complete your student placements before the deadline.'
                        : 'Please submit your assessment form before the deadline.',
                ];
        }
    }

    /**
     * Remove existing deadline notifications for a specific user and deadline
     */
    private function removeExistingDeadlineNotifications(int $userId, int $deadlineId): void
    {
        $deletedCount = Notification::where('user_id', $userId)
            ->where('type', 'hte_deadline')
            ->whereJsonContains('data->deadline_id', $deadlineId)
            ->delete();
            
        if ($deletedCount > 0) {
            Log::info("Removed {$deletedCount} deadline notifications for user {$userId}, deadline {$deadlineId} (assessment already submitted)");
        }
    }

    /**
     * Clean up old deadline notifications
     */
    public function cleanupOldDeadlineNotifications(): void
    {
        $pastDeadlines = Deadline::where('end_date', '<', now())->pluck('id');
        
        if ($pastDeadlines->isNotEmpty()) {
            $deletedCount = Notification::where('type', 'hte_deadline')
                ->where(function ($query) use ($pastDeadlines) {
                    foreach ($pastDeadlines as $deadlineId) {
                        $query->orWhereJsonContains('data->deadline_id', $deadlineId);
                    }
                })
                ->delete();
                
            Log::info("Cleaned up {$deletedCount} old deadline notifications");
        }
    }
}