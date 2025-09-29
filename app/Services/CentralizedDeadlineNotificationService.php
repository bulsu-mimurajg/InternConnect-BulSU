<?php

namespace App\Services;

use App\Models\Deadline;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\UnifiedDeadlineNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CentralizedDeadlineNotificationService
{
    /**
     * Role-to-category mapping for deadline notifications
     */
    private const ROLE_DEADLINE_MAPPING = [
        'admin' => [
            'student_verification',
            'sip_endorsement',
            'student_assessment_form',
            'hte_assessment_form',
            'student_placements_by_hte',
        ],
        'adviser' => [
            'student_verification',
        ],
        'student' => [
            'student_assessment_form',
            'student_placements_by_hte',
        ],
        'hte' => [
            'hte_assessment_form',
            'student_placements_by_hte',
        ],
    ];

    /**
     * Check and create/update deadline notifications for all users
     */
    public function checkAndCreateDeadlineNotifications(): void
    {
        Log::info('Starting centralized deadline notification check');

        // Get all active deadlines
        $deadlines = Deadline::where('status', 'active')
            ->where('end_date', '>', Carbon::now())
            ->get();

        if ($deadlines->isEmpty()) {
            Log::info('No active deadlines found');
            return;
        }

        // Process each deadline for all relevant roles
        foreach ($deadlines as $deadline) {
            $this->processDeadlineForAllRoles($deadline);
        }

        // Cleanup old notifications
        $this->cleanupOldDeadlineNotifications();

        Log::info('Centralized deadline notification check completed');
    }

    /**
     * Process a specific deadline for all relevant roles
     */
    private function processDeadlineForAllRoles(Deadline $deadline): void
    {
        $relevantRoles = $this->getRelevantRolesForDeadline($deadline->category);
        
        if (empty($relevantRoles)) {
            Log::info("No relevant roles found for deadline category: {$deadline->category}");
            return;
        }

        foreach ($relevantRoles as $role) {
            $this->processDeadlineForRole($deadline, $role);
        }
    }

    /**
     * Process a specific deadline for a specific role
     */
    private function processDeadlineForRole(Deadline $deadline, string $role): void
    {
        // Get users with this role
        $users = User::role($role)->get();

        if ($users->isEmpty()) {
            Log::info("No users found for role: {$role}");
            return;
        }

        foreach ($users as $user) {
            $this->processDeadlineForUser($user, $deadline, $role);
        }
    }

    /**
     * Process a specific deadline for a specific user
     */
    private function processDeadlineForUser(User $user, Deadline $deadline, string $role): void
    {
        // Check if user should receive this notification based on their status
        if (!$this->shouldUserReceiveNotification($user, $deadline, $role)) {
            return;
        }

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

        // Check if notification already exists for this deadline and user
        $existingNotification = Notification::where('user_id', $user->id)
            ->where('type', 'unified_deadline')
            ->whereJsonContains('data->deadline_id', $deadline->id)
            ->first();

        $isNewNotification = !$existingNotification;
        $contentChanged = false;

        if ($existingNotification) {
            // Check if content has changed (urgency level, time remaining, etc.)
            $existingData = $existingNotification->data;
            $contentChanged = $this->hasNotificationContentChanged($existingData, $notificationData);
            
            if (!$contentChanged) {
                return; // No need to update
            }
        }

        try {
            // Create notification instance
            $notification = new UnifiedDeadlineNotification(
                $deadline,
                $role,
                $daysRemaining,
                $timeRemaining < 24 ? $timeRemaining : null
            );

            // Send notification using custom EmailService
            $notification->sendCustomEmail($user);

            // Create or update database notification
            if ($isNewNotification) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'unified_deadline',
                    'title' => $notificationData['title'],
                    'message' => $notificationData['message'],
                    'data' => $notificationData['data'],
                    'read_at' => null,
                ]);
                
                Log::info("Created new deadline notification for user {$user->id} ({$role}) for deadline {$deadline->id}");
            } else {
                $existingNotification->update([
                    'title' => $notificationData['title'],
                    'message' => $notificationData['message'],
                    'data' => $notificationData['data'],
                    'read_at' => null, // Mark as unread when updated
                ]);
                
                Log::info("Updated deadline notification for user {$user->id} ({$role}) for deadline {$deadline->id}");
            }

        } catch (\Exception $e) {
            Log::error("Failed to send deadline notification to user {$user->id} ({$role}) for deadline {$deadline->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Check if user should receive notification based on their status
     */
    private function shouldUserReceiveNotification(User $user, Deadline $deadline, string $role): bool
    {
        // Admin always receives notifications
        if ($role === 'admin') {
            return true;
        }

        // Check role-specific conditions
        switch ($role) {
            case 'adviser':
                // Advisers receive student verification notifications
                return $deadline->category === 'student_verification';
                
            case 'student':
                // Students receive notifications unless they've already submitted
                if ($deadline->category === 'student_assessment_form') {
                    return !($user->student && $user->student->is_submit);
                }
                return true; // Always receive placement notifications
                
            case 'hte':
                // HTEs receive notifications unless they've already submitted
                if ($deadline->category === 'hte_assessment_form') {
                    return !($user->hte && $user->hte->is_submit);
                }
                return true; // Always receive placement notifications
        }

        return true;
    }

    /**
     * Get relevant roles for a deadline category
     */
    private function getRelevantRolesForDeadline(string $category): array
    {
        $relevantRoles = [];
        
        foreach (self::ROLE_DEADLINE_MAPPING as $role => $categories) {
            if (in_array($category, $categories)) {
                $relevantRoles[] = $role;
            }
        }
        
        return $relevantRoles;
    }

    /**
     * Get notification data for deadline based on time remaining
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
     * Get urgency level based on time remaining and deadline category
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
     * Get default deadline name based on category
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

    /**
     * Check if notification content has changed
     */
    private function hasNotificationContentChanged(array $existingData, array $newData): bool
    {
        $fieldsToCompare = ['days_remaining', 'hours_remaining', 'urgency_level', 'is_critical'];
        
        foreach ($fieldsToCompare as $field) {
            if (($existingData[$field] ?? null) !== ($newData[$field] ?? null)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Cleanup old deadline notifications
     */
    public function cleanupOldDeadlineNotifications(): void
    {
        // Remove notifications for expired deadlines
        $expiredDeadlines = Deadline::where('end_date', '<', Carbon::now()->subDays(7))->pluck('id');
        
        if ($expiredDeadlines->isNotEmpty()) {
            $deletedCount = Notification::where('type', 'unified_deadline')
                ->whereJsonContains('data->deadline_id', $expiredDeadlines->toArray())
                ->delete();
                
            Log::info("Cleaned up {$deletedCount} old deadline notifications");
        }
    }

    /**
     * Process deadline notifications for a specific user (for testing or manual triggers)
     */
    public function processDeadlineNotificationsForUser(User $user): void
    {
        $userRoles = $user->getRoleNames()->toArray();
        
        if (empty($userRoles)) {
            Log::info("User {$user->id} has no roles, skipping deadline notifications");
            return;
        }

        // Get deadlines relevant to user's roles
        $relevantCategories = [];
        foreach ($userRoles as $role) {
            if (isset(self::ROLE_DEADLINE_MAPPING[$role])) {
                $relevantCategories = array_merge($relevantCategories, self::ROLE_DEADLINE_MAPPING[$role]);
            }
        }

        $relevantCategories = array_unique($relevantCategories);
        
        if (empty($relevantCategories)) {
            Log::info("No relevant deadline categories found for user {$user->id} with roles: " . implode(', ', $userRoles));
            return;
        }

        $deadlines = Deadline::whereIn('category', $relevantCategories)
            ->where('status', 'active')
            ->where('end_date', '>', Carbon::now())
            ->get();

        foreach ($deadlines as $deadline) {
            foreach ($userRoles as $role) {
                if (in_array($deadline->category, self::ROLE_DEADLINE_MAPPING[$role] ?? [])) {
                    $this->processDeadlineForUser($user, $deadline, $role);
                }
            }
        }
    }
}
