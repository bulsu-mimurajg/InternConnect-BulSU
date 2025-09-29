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
        
        // Generate HTML body using Blade template
        $body = view('emails.internship-notification', [
            'studentName' => $studentName,
            'internshipDetails' => $internshipDetails,
        ])->render();

        return $this->sendEmail($studentEmail, $subject, $body, $studentName);
    }

    /**
     * Send assessment reminder email
     */
    public function sendAssessmentReminder($studentEmail, $studentName, $assessmentType)
    {
        $subject = "Assessment Reminder - BULSU InternConnect";
        
        // Generate HTML body using Blade template
        $body = view('emails.assessment-reminder', [
            'studentName' => $studentName,
            'assessmentType' => $assessmentType,
        ])->render();

        return $this->sendEmail($studentEmail, $subject, $body, $studentName);
    }

    /**
     * Send account verification email
     */
    public function sendAccountVerification($userEmail, $userName, $verificationToken)
    {
        $subject = "Account Verification - BULSU InternConnect";
        
        $verificationUrl = url("/verify-account?token={$verificationToken}");
        
        // Generate HTML body using Blade template
        $body = view('emails.account-verification', [
            'userName' => $userName,
            'verificationUrl' => $verificationUrl,
        ])->render();

        return $this->sendEmail($userEmail, $subject, $body, $userName);
    }


}
