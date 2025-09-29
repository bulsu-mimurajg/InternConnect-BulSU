<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Services\EmailService;
use Illuminate\Support\Facades\Log;

class HTEDeadlineNotification extends Notification
{
    use Queueable;

    protected $deadline;
    protected $daysRemaining;
    protected $hoursRemaining;

    /**
     * Create a new notification instance.
     */
    public function __construct($deadline, $daysRemaining = null, $hoursRemaining = null)
    {
        $this->deadline = $deadline;
        $this->daysRemaining = $daysRemaining;
        $this->hoursRemaining = $hoursRemaining;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $deadlineName = $this->deadline['deadline_name'] ?? 'Assessment Form';
        $isPlacement = $this->deadline['category'] === 'student_placements_by_hte';
        $deadlineDate = $this->deadline['deadline_date'] ?? '';
        
        // Use the custom EmailService to send the deadline email
        $emailService = new EmailService();
        
        // Determine subject based on time remaining and category
        $subject = 'HTE Deadline Reminder';
        if ($this->hoursRemaining && $this->hoursRemaining < 24) {
            $subject = 'Deadline Approaching!';
        } elseif ($this->daysRemaining === 1) {
            $subject = '⚠️ Deadline Tomorrow!';
        } elseif ($this->daysRemaining === 3) {
            $subject = 'Deadline in 3 Days';
        } elseif ($this->daysRemaining === 5) {
            $subject = 'Deadline in 5 Days';
        }
        
        // Generate HTML body using Blade template
        $body = view('emails.hte-deadline', [
            'deadlineName' => $deadlineName,
            'deadlineDate' => $deadlineDate,
            'isPlacement' => $isPlacement,
            'daysRemaining' => $this->daysRemaining,
            'hoursRemaining' => $this->hoursRemaining,
        ])->render();

        try {
            $emailService->sendEmail($notifiable->email, $subject, $body, $notifiable->name ?? 'HTE Representative');
        } catch (\Exception $e) {
            // Log the error but don't fail the notification
            Log::error('Failed to send HTE deadline email: ' . $e->getMessage());
        }

        // Return the default Laravel mail message for compatibility
        // Determine the message based on time remaining and category
        if ($this->hoursRemaining && $this->hoursRemaining < 24) {
            $title = 'Deadline Approaching!';
            $message = $isPlacement 
                ? "Your student placements deadline is in {$this->hoursRemaining} hours. Please complete your placements soon to avoid missing the deadline."
                : "Your HTE assessment form deadline is in {$this->hoursRemaining} hours. Please submit your form soon to avoid missing the deadline.";
            $actionText = $isPlacement ? 'Complete Placements' : 'Submit Form';
            $actionUrl = $isPlacement ? url('/hte/endorsement-table') : url('/form');
        } else {
            $urgencyLevel = $this->getUrgencyLevel($this->daysRemaining, $this->deadline['category']);
            $title = $urgencyLevel['title'];
            $message = $isPlacement
                ? "Your student placements deadline is in {$this->daysRemaining} day(s). {$urgencyLevel['message']}"
                : "Your HTE assessment form deadline is in {$this->daysRemaining} day(s). {$urgencyLevel['message']}";
            $actionText = $isPlacement ? 'Manage Placements' : 'Complete Assessment';
            $actionUrl = $isPlacement ? url('/hte/endorsement-table') : url('/form');
        }

        $mailMessage = (new MailMessage)
            ->subject($title)
            ->greeting('Hello!')
            ->line($message)
            ->line("Deadline: {$this->deadline['deadline_date']}")
            ->action($actionText, $actionUrl)
            ->line('Thank you for using BULSU InternConnect!')
            ->salutation('Best regards, BULSU InternConnect Team');

        // Add urgency styling for 1-day reminders
        if ($this->daysRemaining === 1) {
            $mailMessage->error();
        }

        return $mailMessage;
    }

    /**
     * Get urgency level based on days remaining and deadline category
     */
    private function getUrgencyLevel($daysRemaining, $category): array
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
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'deadline_id' => $this->deadline['deadline_id'] ?? null,
            'deadline_name' => $this->deadline['deadline_name'] ?? null,
            'category' => $this->deadline['category'],
            'days_remaining' => $this->daysRemaining,
            'hours_remaining' => $this->hoursRemaining,
        ];
    }
}
