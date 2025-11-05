<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;

class EmailService
{
    private $fromEmail;
    private $fromName;
    private $appPassword;
    private $smtpHost;
    private $smtpPort;
    private $smtpEncryption;
    private $smtpUsername;
    private $smtpTimeout;
    private $smtpHostname;

    public function __construct()
    {
        try {
            // Required configuration - must be set in .env file
            $this->fromEmail = env('PHPMAILER_FROM_EMAIL');
            $this->fromName = env('PHPMAILER_FROM_NAME');
            $this->appPassword = env('PHPMAILER_APP_PASSWORD');
            
            // SMTP Configuration - must be set in .env file
            $this->smtpHost = env('PHPMAILER_SMTP_HOST');
            $this->smtpPort = env('PHPMAILER_SMTP_PORT', 587);
            $this->smtpEncryption = env('PHPMAILER_SMTP_ENCRYPTION', 'tls'); // 'tls' or 'ssl'
            $this->smtpUsername = env('PHPMAILER_SMTP_USERNAME') ?: $this->fromEmail;
            $this->smtpTimeout = env('PHPMAILER_SMTP_TIMEOUT', 30);
            
            // Set hostname for EHLO command - important for SMTP compatibility
            // Defaults to domain from APP_URL or a valid fallback
            $this->smtpHostname = env('PHPMAILER_SMTP_HOSTNAME');
            if (empty($this->smtpHostname)) {
                $appUrl = env('APP_URL', 'http://localhost');
                $parsedUrl = parse_url($appUrl);
                if ($parsedUrl && isset($parsedUrl['host']) && $parsedUrl['host'] !== 'localhost' && $parsedUrl['host'] !== '127.0.0.1') {
                    $this->smtpHostname = $parsedUrl['host'];
                } elseif (!empty($this->fromEmail)) {
                    // Fallback: extract domain from email address
                    $emailDomain = substr(strrchr($this->fromEmail, '@'), 1);
                    if ($emailDomain && $emailDomain !== false) {
                        $this->smtpHostname = $emailDomain;
                    } else {
                        // Last resort: use gmail.com or smtp.gmail.com domain if using Gmail
                        $this->smtpHostname = $this->smtpHost === 'smtp.gmail.com' ? 'gmail.com' : ($this->smtpHost ?? 'localhost');
                    }
                } else {
                    // Last resort: use smtp host domain or default
                    $this->smtpHostname = $this->smtpHost === 'smtp.gmail.com' ? 'gmail.com' : ($this->smtpHost ?? 'localhost');
                }
            }

            // Validate required configuration
            $this->validateConfiguration();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Validate that required email configuration is set
     */
    private function validateConfiguration(): void
    {
        $required = [
            'PHPMAILER_FROM_EMAIL' => $this->fromEmail,
            'PHPMAILER_FROM_NAME' => $this->fromName,
            'PHPMAILER_APP_PASSWORD' => $this->appPassword,
            'PHPMAILER_SMTP_HOST' => $this->smtpHost,
        ];

        $missing = [];
        foreach ($required as $key => $value) {
            if (empty($value)) {
                $missing[] = $key;
            }
        }

        if (!empty($missing)) {
            throw new \Exception(
                'Email configuration is incomplete. Please set the following environment variables in your .env file: ' .
                implode(', ', $missing)
            );
        }
    }

    /**
     * Create and configure a fresh PHPMailer instance
     * This ensures a clean state for each email send
     */
    private function createMailer(): PHPMailer
    {
        $mailer = new PHPMailer(true);
        
        try {
            // Server settings
            $mailer->isSMTP();
            $mailer->Host = $this->smtpHost;
            $mailer->SMTPAuth = true;
            $mailer->Username = $this->smtpUsername;
            $mailer->Password = $this->appPassword;
            
            // Set encryption based on configuration
            if ($this->smtpEncryption === 'ssl') {
                $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            
            $mailer->Port = (int) $this->smtpPort;
            $mailer->Timeout = (int) $this->smtpTimeout;
            
            // Set hostname for EHLO command - critical for SMTP server compatibility
            // This prevents SMTP servers from rejecting connections with "localhost" hostname
            $mailer->Hostname = $this->smtpHostname;
            
            // SMTP debugging disabled by default
            $mailer->SMTPDebug = SMTP::DEBUG_OFF;

            // Set character encoding
            $mailer->CharSet = 'UTF-8';
            
            // Recipients
            $mailer->setFrom($this->fromEmail, $this->fromName);
            $mailer->isHTML(true);
            
            return $mailer;
        } catch (Exception $e) {
            throw new \Exception("Email configuration failed: {$mailer->ErrorInfo}");
        }
    }

    /**
     * Send a simple email
     */
    public function sendEmail($to, $subject, $body, $toName = null)
    {
        $mailer = $this->createMailer();
        
        try {
            // Add recipient
            if ($toName) {
                $mailer->addAddress($to, $toName);
            } else {
                $mailer->addAddress($to);
            }

            // Content
            $mailer->Subject = $subject;
            $mailer->Body = $body;
            $mailer->AltBody = strip_tags($body); // Plain text version

            $mailer->send();
            
            return true;
        } catch (Exception $e) {
            $errorDetails = $mailer->ErrorInfo;
            
            // Log the error
            Log::error('Failed to send email', [
                'to' => $to,
                'subject' => $subject,
                'error' => $errorDetails,
            ]);
            
            // Provide more helpful error messages
            if (strpos($errorDetails, 'Could not authenticate') !== false) {
                $errorDetails .= "\n\nPossible causes:\n";
                $errorDetails .= "1. Incorrect PHPMAILER_APP_PASSWORD in .env file\n";
                $errorDetails .= "2. For Gmail: Make sure you're using an App Password (not your regular password)\n";
                $errorDetails .= "3. 2-Step Verification must be enabled in your Google Account\n";
                $errorDetails .= "4. SMTP username/password mismatch\n";
                $errorDetails .= "\nConfiguration check:\n";
                $errorDetails .= "- SMTP Host: {$this->smtpHost}\n";
                $errorDetails .= "- SMTP Port: {$this->smtpPort}\n";
                $errorDetails .= "- SMTP Username: {$this->smtpUsername}\n";
                $errorDetails .= "- SMTP Hostname (EHLO): {$this->smtpHostname}\n";
                $errorDetails .= "- From Email: {$this->fromEmail}\n";
                $errorDetails .= "\nNote: If emails only work on your PC, ensure PHPMAILER_SMTP_HOSTNAME or APP_URL is set correctly on other machines.\n";
            }
            
            throw new \Exception("Email could not be sent. Error: {$errorDetails}");
        }
    }

    /**
     * Send email with attachment
     */
    public function sendEmailWithAttachment($to, $subject, $body, $attachmentPath, $attachmentName = null, $toName = null)
    {
        $mailer = $this->createMailer();
        
        try {
            // Add recipient
            if ($toName) {
                $mailer->addAddress($to, $toName);
            } else {
                $mailer->addAddress($to);
            }

            // Add attachment
            if (!file_exists($attachmentPath)) {
                throw new \Exception("Attachment file not found: {$attachmentPath}");
            }
            
            if ($attachmentName) {
                $mailer->addAttachment($attachmentPath, $attachmentName);
            } else {
                $mailer->addAttachment($attachmentPath);
            }

            // Content
            $mailer->Subject = $subject;
            $mailer->Body = $body;
            $mailer->AltBody = strip_tags($body);

            $mailer->send();
            
            return true;
        } catch (Exception $e) {
            $errorDetails = $mailer->ErrorInfo;
            
            // Log the error
            Log::error('Failed to send email with attachment', [
                'to' => $to,
                'subject' => $subject,
                'attachment' => $attachmentPath,
                'error' => $errorDetails,
            ]);
            
            throw new \Exception("Email with attachment could not be sent. Error: {$errorDetails}");
        }
    }

    /**
     * Send email to multiple recipients
     */
    public function sendBulkEmail($recipients, $subject, $body)
    {
        $mailer = $this->createMailer();
        
        try {
            // Add multiple recipients
            foreach ($recipients as $recipient) {
                if (is_array($recipient) && isset($recipient['email'])) {
                    $name = $recipient['name'] ?? null;
                    $mailer->addAddress($recipient['email'], $name);
                } else {
                    $mailer->addAddress($recipient);
                }
            }

            // Content
            $mailer->Subject = $subject;
            $mailer->Body = $body;
            $mailer->AltBody = strip_tags($body);

            $mailer->send();
            
            return true;
        } catch (Exception $e) {
            $errorDetails = $mailer->ErrorInfo;
            
            // Log the error
            Log::error('Failed to send bulk email', [
                'recipient_count' => count($recipients),
                'subject' => $subject,
                'error' => $errorDetails,
            ]);
            
            throw new \Exception("Bulk email could not be sent. Error: {$errorDetails}");
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
