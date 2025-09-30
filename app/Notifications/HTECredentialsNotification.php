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
    public function toMail(object $notifiable): MailMessage
    {
        $userName = $this->companyName ?: $notifiable->username ?? 'HTE User';
        $loginUrl = url('/hte/login');
        
        // Use the custom EmailService to send the credentials email
        $emailService = new EmailService();
        
        $subject = "Welcome to BULSU InternConnect - Your HTE Account Credentials";
        
        // Generate HTML body using Blade template
        $body = view('emails.hte-credentials', [
            'username' => $this->username,
            'password' => $this->password,
            'email' => $notifiable->email,
            'companyName' => $this->companyName,
            'loginUrl' => $loginUrl,
        ])->render();

        try {
            $emailService->sendEmail($notifiable->email, $subject, $body, $userName);
        } catch (\Exception $e) {
            // Log the error but don't fail the notification
            Log::error('Failed to send HTE credentials email: ' . $e->getMessage());
        }

        // Return the default Laravel mail message for compatibility
        return (new MailMessage)
            ->subject('Welcome to BULSU InternConnect - Your HTE Account Credentials')
            ->greeting('Welcome to BULSU InternConnect!')
            ->line('Your Host Training Establishment (HTE) account has been successfully created.')
            ->line('**Username:** ' . $this->username)
            ->line('**Password:** ' . $this->password)
            ->line('**Email:** ' . $notifiable->email)
            ->action('Access HTE Dashboard', $loginUrl)
            ->line('Please change your password after your first login for security.')
            ->line('If you have any questions, please contact the support team.');
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
