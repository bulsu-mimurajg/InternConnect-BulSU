<?php

namespace App\Services;

use App\Models\Deadline;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\StudentDeadlineNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentDeadlineNotificationService
{
    /**
     * Check and create/update deadline notifications for Student users
     */
    public function checkAndCreateDeadlineNotifications(): void
    {
        // Get all Student users
        $studentUsers = User::role('student')->get();

        foreach ($studentUsers as $studentUser) {
            $this->processDeadlineNotificationsForUser($studentUser);
        }
    }

    /**
     * Process deadline notifications for a specific Student user
     */
    public function processDeadlineNotificationsForUser(User $studentUser): void
    {
        // Get Student deadlines (assessment form and placement deadlines)
        $studentDeadlines = Deadline::whereIn('category', ['student_assessment_form', 'student_placements_by_hte'])->get();

        foreach ($studentDeadlines as $deadline) {
            $this->processDeadlineForUser($studentUser, $deadline);
        }
    }

    /**
     * Process a specific deadline for a user
     */
    private function processDeadlineForUser(User $studentUser, Deadline $deadline): void
    {
        // Check if this is a student assessment form deadline
        if ($deadline->category === 'student_assessment_form') {
            // Check if the student user has already submitted their assessment form
            if ($studentUser->student && $studentUser->student->is_submit) {
                // User has already submitted, remove any existing notifications and skip
                $this->removeExistingDeadlineNotifications($studentUser->id, $deadline->id);
                return;
            }
        }
        
        // For student placement deadlines, show notifications to all student users
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
        $existingNotification = Notification::where('user_id', $studentUser->id)
            ->where('type', 'student_deadline')
            ->whereJsonContains('data->deadline_id', $deadline->id)
            ->first();

        $isNewNotification = !$existingNotification;

        if ($existingNotification) {
            // Check if the notification content has changed significantly
            $existingData = is_string($existingNotification->data) ? json_decode($existingNotification->data, true) : $existingNotification->data;
            $newData = $notificationData['data'];
            
            $contentChanged = (
                $existingNotification->title !== $notificationData['title'] ||
                $existingNotification->message !== $notificationData['message'] ||
                ($existingData['days_remaining'] ?? null) !== ($newData['days_remaining'] ?? null) ||
                ($existingData['hours_remaining'] ?? null) !== ($newData['hours_remaining'] ?? null)
            );
            
            // Update existing notification
            $updateData = [
                'title' => $notificationData['title'],
                'message' => $notificationData['message'],
                'data' => $notificationData['data'],
                'updated_at' => now(),
            ];
            
            // Update the notification
            $existingNotification->update($updateData);
            
            // If content changed significantly, update created_at to move it to top of list
            if ($contentChanged) {
                \DB::table('notifications')
                    ->where('id', $existingNotification->id)
                    ->update(['created_at' => now()]);
            }
            
            Log::info("Updated deadline notification for Student user {$studentUser->id}, deadline {$deadline->id}" . 
                     ($contentChanged ? ' (content changed, moved to top)' : ''));
        } else {
            // Create new notification
            Notification::create([
                'user_id' => $studentUser->id,
                'type' => 'student_deadline',
                'title' => $notificationData['title'],
                'message' => $notificationData['message'],
                'data' => $notificationData['data'],
                'is_read' => false,
            ]);
            
            Log::info("Created deadline notification for Student user {$studentUser->id}, deadline {$deadline->id}");
        }

        // Send email notification for new notifications or when time remaining changes significantly
        if ($isNewNotification || $contentChanged) {
            $this->sendEmailNotification($studentUser, $deadline, $notificationData);
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
                ? "Student placement deadline is in {$daysRemaining} day(s). {$urgencyLevel['message']}"
                : "Student {$deadlineName} deadline is in {$daysRemaining} day(s). {$urgencyLevel['message']}";
            
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
                ? "Student placement deadline is in {$hoursRemaining} hours. Please complete your placement soon!"
                : "Student {$deadlineName} deadline is in {$hoursRemaining} hours. Please submit your assessment soon!";
                
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
                        ? 'Please complete your student placement immediately to avoid missing the deadline.'
                        : 'Please submit your assessment immediately to avoid missing the deadline.',
                ];
            case 3:
                return [
                    'title' => 'Deadline in 3 Days',
                    'message' => $isPlacement
                        ? 'Please prepare and complete your student placement soon.'
                        : 'Please prepare and submit your assessment soon.',
                ];
            case 5:
                return [
                    'title' => 'Deadline in 5 Days',
                    'message' => $isPlacement
                        ? 'Please start preparing your student placement.'
                        : 'Please start preparing your assessment.',
                ];
            default:
                return [
                    'title' => 'Deadline Reminder',
                    'message' => $isPlacement
                        ? 'Please complete your student placement before the deadline.'
                        : 'Please submit your assessment before the deadline.',
                ];
        }
    }

    /**
     * Remove existing deadline notifications for a specific user and deadline
     */
    private function removeExistingDeadlineNotifications(int $userId, int $deadlineId): void
    {
        $deletedCount = Notification::where('user_id', $userId)
            ->where('type', 'student_deadline')
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
        $pastDeadlines = Deadline::whereIn('category', ['student_assessment_form', 'student_placements_by_hte'])
            ->where('end_date', '<', now())
            ->pluck('id');
        
        if ($pastDeadlines->isNotEmpty()) {
            $deletedCount = Notification::where('type', 'student_deadline')
                ->where(function ($query) use ($pastDeadlines) {
                    foreach ($pastDeadlines as $deadlineId) {
                        $query->orWhereJsonContains('data->deadline_id', $deadlineId);
                    }
                })
                ->delete();
                
            Log::info("Cleaned up {$deletedCount} old student deadline notifications");
        }
    }

    /**
     * Determine if an email reminder should be sent
     */
    private function shouldSendEmailReminder(?Notification $existingNotification, array $notificationData): bool
    {
        if (!$existingNotification) {
            return true; // Always send email for new notifications
        }

        $existingData = is_string($existingNotification->data) ? json_decode($existingNotification->data, true) : $existingNotification->data;
        
        // Send email if days remaining changed (e.g., from 5 days to 3 days)
        $existingDays = $existingData['days_remaining'] ?? null;
        $newDays = $notificationData['data']['days_remaining'] ?? null;
        
        return $existingDays !== $newDays;
    }

    /**
     * Send email notification to Student user
     */
    private function sendEmailNotification(User $studentUser, Deadline $deadline, array $notificationData): void
    {
        try {
            // Validate email address before sending
            if (!$this->isValidEmail($studentUser->email)) {
                Log::warning("Skipping email notification for Student user {$studentUser->id}: Invalid email address '{$studentUser->email}'");
                return;
            }

            $emailData = [
                'deadline_id' => $deadline->id,
                'deadline_name' => $deadline->title,
                'category' => $deadline->category,
                'deadline_date' => $deadline->end_date->format('M d, Y \a\t g:i A'),
                'days_remaining' => $notificationData['data']['days_remaining'] ?? null,
                'hours_remaining' => $notificationData['data']['hours_remaining'] ?? null,
            ];

            $studentUser->notify(new StudentDeadlineNotification($emailData, $emailData['days_remaining'], $emailData['hours_remaining']));
            
            Log::info("Sent deadline email notification to Student user {$studentUser->id} ({$studentUser->email}) for deadline {$deadline->id}");
        } catch (\Exception $e) {
            Log::error("Failed to send deadline email notification to Student user {$studentUser->id}: " . $e->getMessage());
        }
    }

    /**
     * Validate email address format and basic checks
     */
    private function isValidEmail(string $email): bool
    {

        // Basic validation checks
        if (empty($email) || !is_string($email)) {
            return false;
        }

        // Check email format using PHP's built-in filter
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Check for common invalid patterns
        $invalidPatterns = [
            '/^test@/i',
            '/^admin@/i',
            '/^noreply@/i',
            '/^no-reply@/i',
            // Allow example domains only in non-prod; blocked here for prod
            '/@example\./i',
            '/@localhost/i',
            '/@test\./i',
        ];

        foreach ($invalidPatterns as $pattern) {
            if (preg_match($pattern, $email)) {
                return false;
            }
        }

        // Check if email has a valid domain (basic check)
        $domain = substr(strrchr($email, "@"), 1);
        if (empty($domain) || strlen($domain) < 3) {
            return false;
        }

        return true;
    }
}