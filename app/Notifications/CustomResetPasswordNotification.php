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
        
        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #2c3e50; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
                .highlight { background-color: #e74c3c; color: white; padding: 15px; margin: 15px 0; text-align: center; }
                .button { display: inline-block; background-color: #e74c3c; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                .button:hover { background-color: #c0392b; }
                .warning { background-color: #f39c12; color: white; padding: 10px; margin: 10px 0; border-radius: 3px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>BULSU InternConnect</h1>
                    <h2>Password Reset Request</h2>
                </div>
                <div class='content'>
                    <p>Dear {$userName},</p>
                    <p>We received a request to reset your password for your BULSU InternConnect account.</p>
                    
                    <div class='highlight'>
                        <h3>Reset Your Password</h3>
                        <p>Click the button below to reset your password:</p>
                        <a href='{$resetUrl}' class='button'>Reset Password</a>
                    </div>
                    
                    <p><strong>Or copy and paste this link into your browser:</strong></p>
                    <p style='word-break: break-all; background-color: #ecf0f1; padding: 10px; border-radius: 3px;'>{$resetUrl}</p>
                    
                    <div class='warning'>
                        <p><strong>Important Security Information:</strong></p>
                        <ul style='margin: 5px 0; padding-left: 20px;'>
                            <li>This password reset link will expire in 60 minutes</li>
                            <li>If you didn't request this password reset, please ignore this email</li>
                            <li>Your password will remain unchanged until you create a new one</li>
                        </ul>
                    </div>
                    
                    <p>If you're having trouble clicking the button, copy and paste the URL above into your web browser.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from BULSU InternConnect System.</p>
                    <p>© " . date('Y') . " Bulacan State University</p>
                </div>
            </div>
        </body>
        </html>";

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
