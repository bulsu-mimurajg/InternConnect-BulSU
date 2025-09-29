<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
            ->line('Thank you for using InternCity!')
            ->salutation('Best regards, InternCity Team');

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
