<?php

namespace App\Notifications;

use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class InternshipNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $studentName;
    protected $internshipDetails;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $studentName, array $internshipDetails)
    {
        $this->studentName = $studentName;
        $this->internshipDetails = $internshipDetails;
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
    public function toMail(object $notifiable): void
    {
        // Use the custom EmailService to send the internship notification email
        $emailService = new EmailService();
        
        $subject = "Congratulations! You've Been Placed for Internship - BULSU InternConnect";
        
        // Generate HTML body using Blade template
        $body = view('emails.internship-notification', [
            'studentName' => $this->studentName,
            'internshipDetails' => $this->internshipDetails,
        ])->render();

        try {
            $emailService->sendEmail($notifiable->email, $subject, $body, $this->studentName);
        } catch (\Exception $e) {
            // Log the error but don't fail the notification
            Log::error('Failed to send internship notification email: ' . $e->getMessage());
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
            'student_name' => $this->studentName,
            'internship_details' => $this->internshipDetails,
        ];
    }
}
