<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private $mailer;
    private $fromEmail = 'internconnectbulsu@gmail.com';
    private $fromName = 'InternConnect BULSU';
    private $appPassword = 'qtun paed puzf bycw'; // Your Gmail app password

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->configureMailer();
    }

    private function configureMailer()
    {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = 'smtp.gmail.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->fromEmail;
            $this->mailer->Password = $this->appPassword;
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = 587;

            // Recipients
            $this->mailer->setFrom($this->fromEmail, $this->fromName);
            $this->mailer->isHTML(true);
        } catch (Exception $e) {
            throw new \Exception("Email configuration failed: {$this->mailer->ErrorInfo}");
        }
    }

    /**
     * Send a simple email
     */
    public function sendEmail($to, $subject, $body, $toName = null)
    {
        try {
            // Clear previous recipients
            $this->mailer->clearAddresses();
            
            // Add recipient
            if ($toName) {
                $this->mailer->addAddress($to, $toName);
            } else {
                $this->mailer->addAddress($to);
            }

            // Content
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body); // Plain text version

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            throw new \Exception("Email could not be sent. Error: {$this->mailer->ErrorInfo}");
        }
    }

    /**
     * Send email with attachment
     */
    public function sendEmailWithAttachment($to, $subject, $body, $attachmentPath, $attachmentName = null, $toName = null)
    {
        try {
            // Clear previous recipients and attachments
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Add recipient
            if ($toName) {
                $this->mailer->addAddress($to, $toName);
            } else {
                $this->mailer->addAddress($to);
            }

            // Add attachment
            if ($attachmentName) {
                $this->mailer->addAttachment($attachmentPath, $attachmentName);
            } else {
                $this->mailer->addAttachment($attachmentPath);
            }

            // Content
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            throw new \Exception("Email with attachment could not be sent. Error: {$this->mailer->ErrorInfo}");
        }
    }

    /**
     * Send email to multiple recipients
     */
    public function sendBulkEmail($recipients, $subject, $body)
    {
        try {
            // Clear previous recipients
            $this->mailer->clearAddresses();
            
            // Add multiple recipients
            foreach ($recipients as $recipient) {
                if (is_array($recipient) && isset($recipient['email'])) {
                    $name = $recipient['name'] ?? null;
                    $this->mailer->addAddress($recipient['email'], $name);
                } else {
                    $this->mailer->addAddress($recipient);
                }
            }

            // Content
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = strip_tags($body);

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            throw new \Exception("Bulk email could not be sent. Error: {$this->mailer->ErrorInfo}");
        }
    }

    /**
     * Send internship notification email
     */
    public function sendInternshipNotification($studentEmail, $studentName, $internshipDetails)
    {
        $subject = "Internship Placement Notification - BULSU InternConnect";
        
        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #2c3e50; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
                .highlight { background-color: #3498db; color: white; padding: 10px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>BULSU InternConnect</h1>
                    <h2>Internship Placement Notification</h2>
                </div>
                <div class='content'>
                    <p>Dear {$studentName},</p>
                    <p>Congratulations! You have been successfully matched with an internship opportunity.</p>
                    
                    <div class='highlight'>
                        <h3>Internship Details:</h3>
                        <p><strong>Company:</strong> {$internshipDetails['company_name']}</p>
                        <p><strong>Position:</strong> {$internshipDetails['position']}</p>
                        <p><strong>Duration:</strong> {$internshipDetails['duration']}</p>
                        <p><strong>Start Date:</strong> {$internshipDetails['start_date']}</p>
                    </div>
                    
                    <p>Please check your dashboard for more details and next steps.</p>
                    <p>If you have any questions, please contact your adviser or the internship coordinator.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from BULSU InternConnect System.</p>
                    <p>© " . date('Y') . " Bulacan State University</p>
                </div>
            </div>
        </body>
        </html>";

        return $this->sendEmail($studentEmail, $subject, $body, $studentName);
    }

    /**
     * Send assessment reminder email
     */
    public function sendAssessmentReminder($studentEmail, $studentName, $assessmentType)
    {
        $subject = "Assessment Reminder - BULSU InternConnect";
        
        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #e74c3c; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
                .highlight { background-color: #f39c12; color: white; padding: 10px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>BULSU InternConnect</h1>
                    <h2>Assessment Reminder</h2>
                </div>
                <div class='content'>
                    <p>Dear {$studentName},</p>
                    <p>This is a reminder that you have a pending {$assessmentType} assessment.</p>
                    
                    <div class='highlight'>
                        <p><strong>Please complete your assessment as soon as possible.</strong></p>
                    </div>
                    
                    <p>Log in to your dashboard to access and complete the assessment.</p>
                    <p>If you have any questions, please contact your adviser.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated reminder from BULSU InternConnect System.</p>
                    <p>© " . date('Y') . " Bulacan State University</p>
                </div>
            </div>
        </body>
        </html>";

        return $this->sendEmail($studentEmail, $subject, $body, $studentName);
    }

    /**
     * Send account verification email
     */
    public function sendAccountVerification($userEmail, $userName, $verificationToken)
    {
        $subject = "Account Verification - BULSU InternConnect";
        
        $verificationUrl = url("/verify-account?token={$verificationToken}");
        
        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #27ae60; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
                .highlight { background-color: #3498db; color: white; padding: 15px; margin: 15px 0; text-align: center; }
                .button { display: inline-block; background-color: #3498db; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                .button:hover { background-color: #2980b9; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>BULSU InternConnect</h1>
                    <h2>Account Verification</h2>
                </div>
                <div class='content'>
                    <p>Dear {$userName},</p>
                    <p>Welcome to BULSU InternConnect! Thank you for registering with our internship management system.</p>
                    
                    <div class='highlight'>
                        <h3>Please verify your account to get started</h3>
                        <p>Click the button below to verify your email address and activate your account:</p>
                        <a href='{$verificationUrl}' class='button'>Verify My Account</a>
                    </div>
                    
                    <p><strong>Or copy and paste this link into your browser:</strong></p>
                    <p style='word-break: break-all; background-color: #ecf0f1; padding: 10px; border-radius: 3px;'>{$verificationUrl}</p>
                    
                    <p>This verification link will expire in 24 hours for security reasons.</p>
                    <p>If you didn't create an account with BULSU InternConnect, please ignore this email.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from BULSU InternConnect System.</p>
                    <p>© " . date('Y') . " Bulacan State University</p>
                </div>
            </div>
        </body>
        </html>";

        return $this->sendEmail($userEmail, $subject, $body, $userName);
    }


}
