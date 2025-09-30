<?php

namespace App\Services;

use App\Models\Deadline;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\UnifiedDeadlineNotification;
use App\Jobs\ProcessDeadlineNotificationJob;
use App\Jobs\ProcessAllDeadlineNotificationsJob;
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
     * Queue deadline notifications for all users (non-blocking)
     */
    public function queueDeadlineNotifications(): void
    {
        Log::info('Queueing deadline notifications for batch processing');

        // Dispatch the batch job to process all deadline notifications
        ProcessAllDeadlineNotificationsJob::dispatch()
            ->onQueue('deadline-notifications')
            ->delay(now()->addSeconds(5)); // Small delay to ensure database consistency

        Log::info('Deadline notifications queued successfully');
    }

    /**
     * Queue deadline notifications for a specific deadline (for updates)
     */
    public function queueDeadlineNotificationsForDeadline(Deadline $deadline): void
    {
        Log::info('Queueing deadline notifications for specific deadline', [
            'deadline_id' => $deadline->id,
            'deadline_title' => $deadline->title,
            'deadline_category' => $deadline->category,
        ]);

        // Get all users who should receive notifications for this deadline
        $users = $this->getUsersForDeadline($deadline);

        // Queue individual jobs for each user
        foreach ($users as $user) {
            $userRoles = $user->getRoleNames()->toArray();
            foreach ($userRoles as $role) {
                if (in_array($deadline->category, self::ROLE_DEADLINE_MAPPING[$role] ?? [])) {
                    ProcessDeadlineNotificationJob::dispatch($deadline->id, $user->id, $role)
                        ->onQueue('deadline-notifications')
                        ->delay(now()->addSeconds(rand(1, 10))); // Random delay to spread load
                }
            }
        }

        Log::info('Deadline-specific notifications queued successfully', [
            'deadline_id' => $deadline->id,
            'users_count' => $users->count(),
        ]);
    }

    /**
     * Queue deadline notifications for a specific user (non-blocking)
     */
    public function queueDeadlineNotificationsForUser(User $user): void
    {
        Log::info('Queueing deadline notifications for specific user', [
            'user_id' => $user->id,
            'user_email' => $user->email,
        ]);

        // Get deadlines relevant to user's roles
        $userRoles = $user->getRoleNames()->toArray();
        $relevantCategories = [];

        foreach ($userRoles as $role) {
            if (isset(self::ROLE_DEADLINE_MAPPING[$role])) {
                $relevantCategories = array_merge($relevantCategories, self::ROLE_DEADLINE_MAPPING[$role]);
            }
        }

        $relevantCategories = array_unique($relevantCategories);

        if (empty($relevantCategories)) {
            Log::info('No relevant deadline categories found for user', ['user_id' => $user->id]);
            return;
        }

        // Get relevant deadlines
        $deadlines = Deadline::whereIn('category', $relevantCategories)
            ->where('status', 'active')
            ->where('end_date', '>', Carbon::now())
            ->get();

        // Queue individual jobs for each deadline
        foreach ($deadlines as $deadline) {
            foreach ($userRoles as $role) {
                if (in_array($deadline->category, self::ROLE_DEADLINE_MAPPING[$role] ?? [])) {
                    ProcessDeadlineNotificationJob::dispatch($deadline->id, $user->id, $role)
                        ->onQueue('deadline-notifications')
                        ->delay(now()->addSeconds(rand(1, 10))); // Random delay to spread load
                }
            }
        }

        Log::info('User deadline notifications queued successfully', [
            'user_id' => $user->id,
            'deadlines_count' => $deadlines->count(),
        ]);
    }

    /**
     * Get users who should receive notifications for a specific deadline
     */
    private function getUsersForDeadline(Deadline $deadline): \Illuminate\Database\Eloquent\Collection
    {
        $users = collect();

        // Get users based on deadline category with proper filtering
        switch ($deadline->category) {
            case 'student_verification':
                $users = User::whereHas('roles', function ($query) {
                    $query->where('name', 'adviser');
                })
                ->where('status', '!=', 'archived')
                ->whereHas('adviser', function ($query) {
                    $query->where('is_active', true);
                })
                ->get();
                break;
            case 'student_assessment_form':
                $users = User::whereHas('roles', function ($query) {
                    $query->where('name', 'student');
                })
                ->where('status', '!=', 'archived')
                ->whereHas('student', function ($query) {
                    $query->where('is_active', true);
                })
                ->get();
                break;
            case 'hte_assessment_form':
                $users = User::whereHas('roles', function ($query) {
                    $query->where('name', 'hte');
                })
                ->where('status', '!=', 'archived')
                ->whereHas('hte', function ($query) {
                    $query->where('is_active', true);
                })
                ->get();
                break;
            case 'sip_endorsement':
                $users = User::whereHas('roles', function ($query) {
                    $query->where('name', 'admin');
                })
                ->where('status', '!=', 'archived')
                ->get();
                break;
            case 'student_placements_by_hte':
                $users = User::whereHas('roles', function ($query) {
                    $query->whereIn('name', ['admin', 'hte']);
                })
                ->where('status', '!=', 'archived')
                ->where(function ($query) {
                    $query->whereHas('roles', function ($roleQuery) {
                        $roleQuery->where('name', 'admin');
                    })
                    ->orWhere(function ($subQuery) {
                        $subQuery->whereHas('roles', function ($roleQuery) {
                            $roleQuery->where('name', 'hte');
                        })
                        ->whereHas('hte', function ($hteQuery) {
                            $hteQuery->where('is_active', true);
                        });
                    });
                })
                ->get();
                break;
        }

        Log::info("Retrieved users for deadline category: {$deadline->category}", [
            'deadline_id' => $deadline->id,
            'deadline_category' => $deadline->category,
            'users_count' => $users->count(),
            'user_emails' => $users->pluck('email')->toArray(),
        ]);

        return $users;
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
            
            // Always send email for deadline updates, even if content hasn't changed significantly
            // This ensures users get updated information when deadlines are modified
            if (!$contentChanged) {
                // Still send email but don't update database notification
                Log::info("Content unchanged but sending updated email for deadline {$deadline->id} to user {$user->id}");
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
                // Always update existing notification with new time information
                $existingNotification->update([
                    'title' => $notificationData['title'],
                    'message' => $notificationData['message'],
                    'data' => $notificationData['data'],
                    'read_at' => null, // Mark as unread when updated
                    'updated_at' => now(), // Update timestamp
                ]);
                
                Log::info("Updated deadline notification for user {$user->id} ({$role}) for deadline {$deadline->id} with new time information");
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
            Log::info("Admin user {$user->id} should receive notification for deadline {$deadline->id}");
            return true;
        }

        // Check role-specific conditions
        switch ($role) {
            case 'adviser':
                // Advisers receive student verification notifications
                $shouldReceive = $deadline->category === 'student_verification';
                Log::info("Adviser user {$user->id} should receive notification: " . ($shouldReceive ? 'YES' : 'NO'), [
                    'deadline_category' => $deadline->category,
                    'user_id' => $user->id,
                ]);
                return $shouldReceive;
                
            case 'student':
                // Students receive notifications unless they've already submitted
                if ($deadline->category === 'student_assessment_form') {
                    $hasSubmitted = $user->student && $user->student->is_submit;
                    $shouldReceive = !$hasSubmitted;
                    Log::info("Student user {$user->id} should receive notification: " . ($shouldReceive ? 'YES' : 'NO'), [
                        'deadline_category' => $deadline->category,
                        'user_id' => $user->id,
                        'has_student_record' => $user->student ? 'YES' : 'NO',
                        'is_submit' => $hasSubmitted,
                    ]);
                    return $shouldReceive;
                }
                
                // For placement-related deadlines, only notify students who are actually placed
                // or have endorsed matches (not just endorsement records that might be inconsistent)
                if ($deadline->category === 'student_placements_by_hte') {
                    $isPlaced = $user->student && $user->student->is_placed;
                    
                    // Check if student has any matches that are actually endorsed (not just endorsement records)
                    $hasEndorsedMatches = $user->student && $user->student->matches()
                        ->where('endorsement_status', 'endorsed')
                        ->exists();
                    
                    $shouldReceive = $isPlaced || $hasEndorsedMatches;
                    
                    Log::info("Student user {$user->id} should receive placement notification: " . ($shouldReceive ? 'YES' : 'NO'), [
                        'deadline_category' => $deadline->category,
                        'user_id' => $user->id,
                        'has_student_record' => $user->student ? 'YES' : 'NO',
                        'is_placed' => $isPlaced,
                        'has_endorsed_matches' => $hasEndorsedMatches,
                    ]);
                    return $shouldReceive;
                }
                
                Log::info("Student user {$user->id} should receive notification: YES (default)", [
                    'deadline_category' => $deadline->category,
                    'user_id' => $user->id,
                ]);
                return true; // Default behavior for other deadline categories
                
            case 'hte':
                // HTEs receive notifications unless they've already submitted
                if ($deadline->category === 'hte_assessment_form') {
                    $hasSubmitted = $user->hte && $user->hte->is_submit;
                    $shouldReceive = !$hasSubmitted;
                    Log::info("HTE user {$user->id} should receive notification: " . ($shouldReceive ? 'YES' : 'NO'), [
                        'deadline_category' => $deadline->category,
                        'user_id' => $user->id,
                        'has_hte_record' => $user->hte ? 'YES' : 'NO',
                        'is_submit' => $hasSubmitted,
                    ]);
                    return $shouldReceive;
                }
                Log::info("HTE user {$user->id} should receive notification: YES (placement notification)", [
                    'deadline_category' => $deadline->category,
                    'user_id' => $user->id,
                ]);
                return true; // Always receive placement notifications
        }

        Log::info("User {$user->id} with role {$role} should receive notification: YES (default)", [
            'deadline_category' => $deadline->category,
            'user_id' => $user->id,
        ]);
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
            } else {
                $level = 'low';
                $title = 'Deadline Reminder';
                $message = 'Please plan to complete this task in the coming weeks.';
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
     * Process deadline notifications for a specific user and specific deadline
     */
    public function processDeadlineNotificationForUserAndDeadline(User $user, Deadline $deadline, string $role): void
    {
        Log::info('Processing deadline notification for specific user and deadline', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'deadline_id' => $deadline->id,
            'deadline_title' => $deadline->title,
            'deadline_category' => $deadline->category,
            'user_role' => $role,
        ]);

        // Check if user should receive this notification based on their status
        if (!$this->shouldUserReceiveNotification($user, $deadline, $role)) {
            Log::info("User {$user->id} should not receive notification for deadline {$deadline->id} with role {$role} - SKIPPING");
            return;
        }

        Log::info("User {$user->id} should receive notification for deadline {$deadline->id} with role {$role} - PROCEEDING");

        $now = Carbon::now();
        $deadlineDate = Carbon::parse($deadline->end_date);
        
        // Calculate time remaining
        $timeRemaining = $now->diffInHours($deadlineDate, false);
        $daysRemaining = round($now->diffInDays($deadlineDate, false));
        
        // Only process if deadline is in the future
        if ($timeRemaining <= 0) {
            Log::info("Deadline {$deadline->id} has already passed, skipping notification");
            return;
        }

        $notificationData = $this->getNotificationDataForDeadline($deadline, $timeRemaining, $daysRemaining);
        
        if (!$notificationData) {
            Log::info("No notification needed for deadline {$deadline->id} (not in 1, 3, 5 days or < 24 hours)");
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
            
            // Always send email for deadline updates, even if content hasn't changed significantly
            // This ensures users get updated information when deadlines are modified
            if (!$contentChanged) {
                // Still send email but don't update database notification
                Log::info("Content unchanged but sending updated email for deadline {$deadline->id} to user {$user->id}");
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
                // Always update existing notification with new time information
                $existingNotification->update([
                    'title' => $notificationData['title'],
                    'message' => $notificationData['message'],
                    'data' => $notificationData['data'],
                    'read_at' => null, // Mark as unread when updated
                    'updated_at' => now(), // Update timestamp
                ]);
                
                Log::info("Updated deadline notification for user {$user->id} ({$role}) for deadline {$deadline->id} with new time information");
            }

        } catch (\Exception $e) {
            Log::error("Failed to send deadline notification to user {$user->id} ({$role}) for deadline {$deadline->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
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
