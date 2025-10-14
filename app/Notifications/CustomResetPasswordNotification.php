<?php

namespace App\Notifications;

use App\Services\EmailService;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class CustomResetPasswordNotification extends ResetPasswordNotification
{
    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $resetUrl = $this->resetUrl($notifiable);
        $userName = $notifiable->name ?? $notifiable->username ?? 'User';
        
        // Use the custom EmailService to send the password reset email
        $emailService = new EmailService();
        
        $subject = "Password Reset Request - BULSU InternConnect";
        
        // Generate HTML body using Blade template
        $body = view('emails.password-reset', [
            'userName' => $userName,
            'resetUrl' => $resetUrl,
        ])->render();

        try {
            $emailService->sendEmail($notifiable->email, $subject, $body, $userName);
        } catch (\Exception $e) {
            // Log the error but don't fail the notification
            Log::error('Failed to send password reset email: ' . $e->getMessage());
        }
        
        // Return a MailMessage to prevent the "view on null" error
        // This won't be used since we're sending via EmailService above
        return (new MailMessage)
            ->subject($subject)
            ->line('Password reset email sent successfully.');
    }

    /**
     * Override the resetUrl method to use proper URL generation
     */
    protected function resetUrl($notifiable): string
    {
        return route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->email,
        ]);
    }
}
