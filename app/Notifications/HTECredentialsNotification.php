<?php

namespace App\Notifications;

use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class HTECredentialsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 1; // Retry once only
    public $timeout = 60; // 60 seconds timeout

    private $username;
    private $password;
    private $companyName;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $username, string $password, ?string $companyName = null)
    {
        $this->username = $username;
        $this->password = $password;
        $this->companyName = $companyName;
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
        try {
            // Validate email address
            if (empty($notifiable->email)) {
                throw new \Exception("Email address is missing for HTE user");
            }

            $userName = $this->companyName ?: $notifiable->username ?? 'HTE User';
            $loginUrl = config('app.url') . '/login';
            
            // Use the custom EmailService to send the credentials email
            $emailService = new EmailService();
            
            $subject = "Welcome to BULSU InternConnect - Your HTE Account Credentials";
            
            // Generate HTML body using Blade template
            try {
                $body = view('emails.hte-credentials', [
                    'username' => $this->username,
                    'password' => $this->password,
                    'email' => $notifiable->email,
                    'companyName' => $this->companyName,
                    'loginUrl' => $loginUrl,
                ])->render();
            } catch (\Exception $e) {
                throw new \Exception("Failed to render email template: " . $e->getMessage());
            }

            // Send email
            $emailService->sendEmail($notifiable->email, $subject, $body, $userName);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to send HTE credentials email', [
                'email' => $notifiable->email ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            // Re-throw the exception so Laravel can mark the job as failed
            throw $e;
        }
    }

    /**
     * Handle a job failure after all retries are exhausted.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('HTE credentials notification failed', [
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'username' => $this->username,
            'email' => $notifiable->email,
            'company_name' => $this->companyName,
        ];
    }
}
