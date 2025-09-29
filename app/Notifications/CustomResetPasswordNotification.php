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
        
        // Use the custom EmailService to send the password reset email
        $emailService = new EmailService();
        
        $userName = $notifiable->name ?? $notifiable->username ?? 'User';
        
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

        // Return the default Laravel mail message for compatibility
        return (new MailMessage)
            ->subject('Password Reset Request - BULSU InternConnect')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $resetUrl)
            ->line('This password reset link will expire in 60 minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }
}
