<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Services\EmailService;
use Illuminate\Support\Facades\Log;

class UnifiedDeadlineNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $deadline;
    protected $daysRemaining;
    protected $hoursRemaining;
    protected $userRole;
    protected $urgencyLevel;

    /**
     * Create a new notification instance.
     */
    public function __construct($deadline, $userRole, $daysRemaining = null, $hoursRemaining = null)
    {
        $this->deadline = $deadline;
        $this->userRole = $userRole;
        $this->daysRemaining = $daysRemaining;
        $this->hoursRemaining = $hoursRemaining;
        $this->urgencyLevel = $this->getUrgencyLevel($daysRemaining, $hoursRemaining, $deadline->category);
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): void
    {
        // Use custom EmailService instead of Laravel Mail
        $this->sendCustomEmail($notifiable);
    }

    /**
     * Send the notification using custom EmailService
     */
    public function sendCustomEmail(object $notifiable): void
    {
        try {
            $emailService = new EmailService();
            
            $deadlineName = $this->deadline->title ?? $this->getDefaultDeadlineName();
            $timeRemaining = $this->getTimeRemainingText();
            $actionText = $this->getActionText();
            $actionUrl = $this->getActionUrl();
            $userDisplayName = $this->getUserDisplayName($notifiable);
            $categoryDisplay = $this->deadline->getCategoryDisplayName();
            $roleSpecificContent = $this->getRoleSpecificContent();

            // Generate HTML body using the blade template
            $body = view('emails.unified-deadline', [
                'urgencyLevel' => $this->urgencyLevel,
                'userDisplayName' => $userDisplayName,
                'deadlineName' => $deadlineName,
                'categoryDisplay' => $categoryDisplay,
                'deadlineDate' => $this->deadline->end_date,
                'timeRemainingText' => $timeRemaining,
                'daysRemaining' => $this->daysRemaining,
                'hoursRemaining' => $this->hoursRemaining,
                'actionText' => $actionText,
                'actionUrl' => $actionUrl,
                'roleSpecificContent' => $roleSpecificContent,
            ])->render();

            // Send email using custom EmailService
            $emailService->sendEmail(
                $notifiable->email,
                $this->urgencyLevel['title'],
                $body,
                $userDisplayName
            );

            Log::info('Deadline notification sent via custom EmailService', [
                'user_id' => $notifiable->id,
                'user_email' => $notifiable->email,
                'user_role' => $this->userRole,
                'deadline_id' => $this->deadline->id,
                'deadline_category' => $this->deadline->category,
                'urgency_level' => $this->urgencyLevel['level'],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send deadline notification via custom EmailService', [
                'user_id' => $notifiable->id,
                'user_email' => $notifiable->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Re-throw the exception so it can be handled by the notification system
            throw $e;
        }
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'deadline_id' => $this->deadline->id,
            'deadline_name' => $this->deadline->title ?? $this->getDefaultDeadlineName(),
            'category' => $this->deadline->category,
            'category_display' => $this->deadline->getCategoryDisplayName(),
            'deadline_date' => $this->deadline->end_date,
            'days_remaining' => $this->daysRemaining,
            'hours_remaining' => $this->hoursRemaining,
            'urgency_level' => $this->urgencyLevel['level'],
            'user_role' => $this->userRole,
            'is_critical' => $this->urgencyLevel['is_critical'],
        ];
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
    private function getDefaultDeadlineName(): string
    {
        return match($this->deadline->category) {
            'student_verification' => 'Student Verification',
            'student_assessment_form' => 'Student Assessment Form',
            'hte_assessment_form' => 'HTE Assessment Form',
            'internship_placement' => 'Internship Placement',
            // Legacy support
            'sip_endorsement' => 'SIP Endorsement',
            'student_placements_by_hte' => 'Student Placements',
            default => 'Assessment Form',
        };
    }

    /**
     * Get time remaining text
     */
    private function getTimeRemainingText(): string
    {
        if ($this->daysRemaining !== null) {
            if ($this->daysRemaining == 1) {
                return 'due tomorrow';
            } elseif ($this->daysRemaining == 0) {
                return 'due today';
            } else {
                return "due in {$this->daysRemaining} days";
            }
        } elseif ($this->hoursRemaining !== null) {
            if ($this->hoursRemaining == 1) {
                return 'due in 1 hour';
            } else {
                return "due in {$this->hoursRemaining} hours";
            }
        }
        
        return 'approaching';
    }

    /**
     * Get action text based on user role and deadline category
     */
    private function getActionText(): string
    {
        return match([$this->userRole, $this->deadline->category]) {
            ['admin', 'student_verification'] => 'Please review and verify student applications.',
            ['admin', 'internship_placement'], ['admin', 'sip_endorsement'] => 'Please process internship placements for students.',
            ['adviser', 'student_verification'] => 'Please verify your assigned students.',
            ['student', 'student_assessment_form'] => 'Please complete your assessment form.',
            ['student', 'internship_placement'], ['student', 'student_placements_by_hte'] => 'Please check your placement status.',
            ['hte', 'hte_assessment_form'] => 'Please complete your HTE assessment form.',
            ['hte', 'internship_placement'], ['hte', 'student_placements_by_hte'] => 'Please complete student placements.',
            default => 'Please complete the required actions for this deadline.',
        };
    }

    /**
     * Get action URL based on user role
     */
    private function getActionUrl(): string
    {
        return match($this->userRole) {
            'admin' => '/admin/events',
            'adviser' => '/adviser/dashboard',
            'student' => '/student/dashboard',
            'hte' => '/hte/dashboard',
            default => '/dashboard',
        };
    }

    /**
     * Get user display name
     */
    private function getUserDisplayName($notifiable): string
    {
        return match($this->userRole) {
            'admin' => 'Administrator',
            'adviser' => $notifiable->adviser->adviser_fname ?? 'Adviser',
            'student' => $notifiable->student->first_name ?? 'Student',
            'hte' => $notifiable->hte->cperson_fname ?? 'HTE Representative',
            default => 'User',
        };
    }

    /**
     * Get role-specific content for the notification
     */
    private function getRoleSpecificContent(): ?string
    {
        return match([$this->userRole, $this->deadline->category]) {
            ['admin', 'student_verification'] => 'As an administrator, you need to review and verify student applications.',
            ['admin', 'internship_placement'], ['admin', 'sip_endorsement'] => 'As an administrator, you need to process internship placements for students. This includes both endorsements and final placements.',
            ['admin', 'student_assessment_form'] => 'Monitor student assessment completion and provide support as needed.',
            ['admin', 'hte_assessment_form'] => 'Monitor HTE assessment completion and provide support as needed.',
            ['admin', 'student_placements_by_hte'] => 'Monitor the placement process and ensure all deadlines are met.',
            ['adviser', 'student_verification'] => 'As an adviser, you need to verify your assigned students.',
            ['student', 'student_assessment_form'] => 'Complete your self-assessment to help us match you with the best internship opportunities.',
            ['student', 'internship_placement'], ['student', 'student_placements_by_hte'] => 'Check your dashboard to see your placement status and any required actions.',
            ['hte', 'hte_assessment_form'] => 'Complete your HTE assessment form to provide internship opportunities for students.',
            ['hte', 'internship_placement'], ['hte', 'student_placements_by_hte'] => 'Complete student placements to finalize the internship matching process.',
            default => null,
        };
    }
}
