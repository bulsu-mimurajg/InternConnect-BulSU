<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentDeadlineNotification extends Notification
{
    use Queueable;

    protected array $deadlineData;
    protected ?int $daysRemaining;
    protected ?int $hoursRemaining;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $deadlineData, ?int $daysRemaining = null, ?int $hoursRemaining = null)
    {
        $this->deadlineData = $deadlineData;
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
        $deadlineName = $this->deadlineData['deadline_name'] ?? 'Assessment Deadline';
        $deadlineDate = $this->deadlineData['deadline_date'] ?? '';
        
        // Format the deadline date
        $formattedDate = '';
        if ($deadlineDate) {
            try {
                $date = new \DateTime($deadlineDate);
                $formattedDate = $date->format('M d, Y \\a\\t g:i A');
            } catch (\Exception $e) {
                $formattedDate = $deadlineDate;
            }
        }

        $message = new MailMessage;
        
        // Determine subject and content based on time remaining
        if ($this->daysRemaining) {
            if ($this->daysRemaining === 1) {
                $message->subject('⚠️ Student Assessment Deadline Tomorrow!')
                        ->greeting('Urgent: Assessment Deadline Tomorrow!')
                        ->line("Your student assessment deadline is tomorrow ({$formattedDate}).")
                        ->line('Please complete your assessment immediately to avoid missing the deadline.')
                        ->action('Complete Assessment', url('/assessment'))
                        ->line('If you have already completed your assessment, please ignore this email.');
            } elseif (in_array($this->daysRemaining, [3, 5])) {
                $message->subject("Student Assessment Deadline in {$this->daysRemaining} Days")
                        ->greeting("Assessment Deadline Reminder")
                        ->line("Your student assessment deadline is in {$this->daysRemaining} days ({$formattedDate}).")
                        ->line('Please prepare and submit your assessment soon to avoid any last-minute issues.')
                        ->action('Complete Assessment', url('/assessment'))
                        ->line('If you have already completed your assessment, please ignore this email.');
            } else {
                $message->subject('Student Assessment Deadline Updated')
                        ->greeting('Assessment Deadline Update')
                        ->line("Your student assessment deadline has been updated to {$formattedDate}.")
                        ->line('Please ensure you complete your assessment before the deadline.')
                        ->action('Complete Assessment', url('/assessment'));
            }
        } elseif ($this->hoursRemaining) {
            $message->subject('⚠️ Assessment Deadline in ' . $this->hoursRemaining . ' Hours!')
                    ->greeting('Urgent: Assessment Deadline Approaching!')
                    ->line("Your student assessment deadline is in {$this->hoursRemaining} hours ({$formattedDate}).")
                    ->line('Please submit your assessment immediately to avoid missing the deadline.')
                    ->action('Complete Assessment', url('/assessment'))
                    ->line('If you have already completed your assessment, please ignore this email.');
        } else {
            // General deadline update
            $message->subject('Student Assessment Deadline Update')
                    ->greeting('Assessment Deadline Update')
                    ->line("Your student assessment deadline has been updated to {$formattedDate}.")
                    ->line('Please ensure you complete your assessment before the deadline.')
                    ->action('Complete Assessment', url('/assessment'));
        }

        return $message->line('Thank you for your attention to this matter.')
                      ->salutation('Best regards,');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'deadline_id' => $this->deadlineData['deadline_id'] ?? null,
            'deadline_name' => $this->deadlineData['deadline_name'] ?? null,
            'deadline_date' => $this->deadlineData['deadline_date'] ?? null,
            'category' => $this->deadlineData['category'] ?? null,
            'days_remaining' => $this->daysRemaining,
            'hours_remaining' => $this->hoursRemaining,
        ];
    }
}